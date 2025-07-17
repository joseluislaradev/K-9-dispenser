//Include libraries
#include <HTTPClient.h> // Libreria para las solicitudes http
#include <WiFi.h>       //Libreria para utilizar wifi
#include <ESP32Servo.h> // Libreria para mover los servos
#include <ESPAsyncWebSrv.h> //Libreria para crear una red wifi temporal
#include <Preferences.h> //Libreria para utilizar la memoria NVS, la placa ESP32 ya la tiene integrada (EEPROM ya quedo absoleta)
#include <string.h> //Para manejar cadenas de caracteres
#include <HX711.h> // Libreria balanza


Preferences preferences;

// Definición de la estructura de config de la memoria NVS, esto guardara las credenciale de la red cuando estemos en modo configuracion
String ssid;
String password;

// Definición de la estructura de info de la memoria NVS, guardara el id del dueño del dispnesador, y el id del dispensador cuando estemos en modo configuracion
int id = 0;
int idDispensador = 0;

// Punto de acceso WiFi temporal
const char* apSSID = "k-9 dispenser";
const char* apPassword = "";

/*Configurando especificaciones del dispositivo
const char* modelo = "Core";
const char* numeroSerie = "k9-20230713-Core-1 ";
*/

AsyncWebServer server(80); //El puerto 80 es el numero estandar donde el servidor web escucha las solicitudes entrantes 

//Otras variables
int ayuda = 0; //Lo uso para poner que si es 0 ya puede hacer el loop pero si es diferente de 0 no puede hacerlo
int estadoMotor = 0; //Parecido al de arriba, cuando vale 0 quiere decir que esta apagado el motor y puedo volver a presionar el boton fisico

int comidaDisponibleActual = 700; //Guarda el valor del almacenamiento de comida principal detectado del sensor
int comidaPlatoActual = 0; //Guarda el valor de la comida que hay en el plato detectado del sensor
int cantidad = 0; // Guarda la cantidad de comida a dispensar traido el valor de la base de datos

int response_code = 0;
String response_body = "";

Servo servo1; // Lado izquierdo puerta
Servo servo2; // Lado derecho puerta

//Pines balanza
const int LOADCELL_DOUT_PIN = 19;
const int LOADCELL_SCK_PIN = 18;

HX711 balanza;

//Variables used in the code
String motor_id = "1";                  //Just in case you control more than 1 LED
bool toggle_pressed = false;          //Each time we press the push button    
String data_to_send = "";             //Text data to send to the server
unsigned int Actual_Millis, Previous_Millis;
int refresh_time = 200;               //Refresh rate of connection to website (recommended more than 1s)


//Inputs/outputs
int button1 = 14;                     //Connect push button on this pin
int LED = 2;                          //Connect LED on this pin (add 150ohm resistor)


//Button press interruption
void IRAM_ATTR isr() {
  toggle_pressed = true; 
}


//Revisa si la memoria NVS esta vacia
bool isNVSEmpty() {
  //Obteniendo el id guardado
  preferences.begin("info", false);
  id = preferences.getInt("idUsuario", 0);
  idDispensador = preferences.getInt("idDispensador", 0);
  preferences.end();

  //Obteniendo las credneciales guardadas de la red
  preferences.begin("config", false);
  ssid = preferences.getString("wifiSSID", "");
  password = preferences.getString("wifiPassword", "");
  preferences.end();

  if(ssid == "" || password == ""){
    return true; //Retorna true si no hay credenciales guardades
  } else {
    return false; // Retorna false si hay credenciales guardadas
  }
}

//recibe solicitud con el id del usuario
void recibirId(AsyncWebServerRequest* request) {
  if (request->args() > 0) { // Verifica que la solicitud HTTP tenga argumentos, es decir, si se envio una solicitud POST
    for (uint8_t i = 0; i < request->args(); i++) { //recorre los argumentos de la solicitud
      String argName = request->argName(i); //Obtiene nombre del argumento actual
      String argValue = request->arg(i); //Obtiene valor del argumento actual
      if (argName == "id") { // Si lo que recibe del post es wifiSSID
        id = argValue.toInt();  //Guarda el valor del id en la estructura datos
      } 
    } 

    Serial.println("Id del usuario dueño recibido: " + String(id));

    preferences.begin("info", false);
    int idrecuperado = preferences.getInt("idUsuario", 0);
    preferences.end();

    if(idrecuperado != id){ // Si el id guardado no es igual al que recibio guardado lo tiene que guardar
      //Guardando o reemplazando datos en la namespace info
      preferences.begin("info", false);
      preferences.putInt("idUsuario", id);
      idrecuperado = preferences.getInt("idUsuario", 0);
      preferences.end();
    }

    preferences.begin("info", false);
    idrecuperado = preferences.getInt("idUsuario", 0);
    Serial.println("El id recuperado es: " + String(idrecuperado));
    preferences.end();

    String html = "<html><body>";
    html += "<script>";
    html += "window.location.href = 'http://192.168.4.1/config';";
    html += "</script>";
    html += "</body></html>";

    request->send(200, "text/html",html);
    return;
  }

  //Si entro directamnete con la url debe de dar error
  String html = "<html><body>";
  html += "<h1>Error de Acceso. Inicia Sesion y registra tu dispositivo desde la pagina web</h1>";
  html += "</body></html>";
  request->send(200, "text/html", html);
}

//maneja la solicitud de la configuracion wifi, cuando ya se tiene el id muestra el formulario para crendenciales wifi, si no hay id muestra error de acceso
void handleConfigRequest(AsyncWebServerRequest* request) {
  if (request->args() > 0) { // Verifica que la solicitud HTTP tenga argumentos, es decir, si se envio una solicitud POST
    for (uint8_t i = 0; i < request->args(); i++) { //recorre los argumentos de la solicitud
      String argName = request->argName(i); //Obtiene nombre del argumento actual
      String argValue = request->arg(i); //Obtiene valor del argumento actual
      if (argName == "wifiSSID") { // Si lo que recibe del post es wifiSSID
        ssid = argValue;  //Guarda el ssid en la esturctura configuracion(que es una vairable de la estructura config)
      } else if (argName == "wifiPassword") {  //Si lo que recibe del post es wifiPassword
        password = argValue;  //Guarda el password en la estructura configuracion (que es una vairable de la estructura config)
      } 
    } 

    Serial.println("SSID recibido: " + ssid);
    Serial.println("Password recibido: " + password);

    //Guardando o reemplazando datos en la namespace config
    preferences.begin("config", false);
    preferences.putString("wifiSSID", ssid); //Guardando SSID
    ssid = preferences.getString("wifiSSID", ""); //Recuperando valor
    Serial.println("El SSID recuperado es: " + ssid);
    preferences.putString("wifiPassword", password);
    password = preferences.getString("wifiPassword", "");
    Serial.println("El password recuperado es: " + password);
    preferences.end();

    String html = "<html><body>";
    html += "<script>";
    html += "window.location.href = 'https://k9dispenser.000webhostapp.com/public_html/indexUsuario.php';";
    html += "</script>";
    html += "</body></html>";

    request->send(200, "text/html",html);

    delay(3000);
    ESP.restart();
    return;
  }

  //Si no hubo una solicitud quiere decir que apenas se hara por lo que generamos una pagina para que envie sus datos el usuario

  preferences.begin("info", false);
  id = preferences.getInt("idUsuario", 0);
  preferences.end();

  if(id != 0){ // Si ya se recibio un id de usuario, es decir entraron desde la web y no poniendo la URL directamente
    //Creo formulario para que ingrese los datos
    String html = R"HTML(<!DOCTYPE html>
    <html lang="en">
      <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>k-9 dispenser</title>
        <style>
          body {
            background-color: rgb(0, 0, 0);
            color: white;
          }

          a {
            text-decoration: none;
            color: inherit;
          }

          .card {
            background-color: rgb(25, 28, 36);
            color: white;
            margin: 0 auto;
            max-width: 400px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
          }

          .btn-primary {
            background-color: blue;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-right: 10px;
          }

          .btn-primary:hover {
            background-color: darkblue;
          }

          .btn-dark {
            background-color: black;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
          }

          .btn-dark:hover {
            background-color: #333;
          }

          .form-group {
            font-family: "Montserrat", sans-serif;
            font-size: 14px;
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
          }

          .form-group label {
            color: white;
            margin-bottom: 5px;
          }

          .form-group input {
            height: 40px;
            background-color: rgba(42, 48, 56, 0.9);
            padding: 5px;
            border-radius: 5px;
            border: none;
            color: white;
          }

          .card-description {
            color: rgb(177, 178, 178);
          }

          .card-title {
            font-family: "Montserrat", sans-serif;
            font-weight: bold;
            font-weight: 500;
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
            line-height: 1.2;
          }


        </style>
      </head>
      <body>
        <div class="content-wrapper" align="center">
          <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
              <div class="card-body">
                <h4 class="card-title" align="center">INGRESA LAS CREDENCIALES DE TU WIFI</h4>
                <br>
                <p class="card-description">Puedes consultar el nombre y contraseña de tu internet en tu modem wifi siempre y cuando nunca hayas cambiado la contraseña.</p>
                <br>
                <form class="forms-sample" action="/config" method="POST">
                  <div class="form-group">
                    <label for="wifiSSID">Ingresa el nombre de tu red (SSID):</label>
                    <input type="text" class="form-control" id="wifiSSID" name="wifiSSID" placeholder="INFINITUM5C45_2.4" required>
                  </div>
                  <br>
                  <div class="form-group">
                    <label for="wifiPassword">Ingresa la contraseña de tu red:</label>
                    <input type="text" class="form-control" id="wifiPassword" name="wifiPassword" placeholder="PJas5JrdAT" required>
                  </div>
                  <br>
                  <div align="center">
                    <button type="submit" class="btn btn-primary">CONECTAR</button>
                    <a href="http://localhost/k9dispenser/Vista/indexUsuario.php" class="btn btn-dark">cancelar</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <footer class="footer">
          <div class="d-sm-flex justify-content-center justify-content-sm-between">
            <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">© k9dispenser.com 2023</span>
          </div>
        </footer>
      </body>
    </html>
    )HTML";

    request->send(200, "text/html", html);
    
  } else {
    //Si no hubo una solcitud quiere decir que apenas se hara por lo que generamos una pagina para que envie sus datos el usuario
    String html = "<html><body>";
    html += "<h1>Error de Acceso. Inicia Sesion y registra tu dispositivo desde la pagina web o solo recarga la pagina.</h1>";
    html += "</body></html>";
    request->send(200, "text/html", html);
  }
}

//envia el usuario que registro el dispensador, ademas de la clave y contraseña wifi registrada y regresa el id del dispensador
void envioCredenciales(){
  response_code = 0;
  do{
    //We make the refresh loop using millis() so we don't have to sue delay();
    Actual_Millis = millis();
    if(Actual_Millis - Previous_Millis > refresh_time){
      Previous_Millis = Actual_Millis;  
      if(WiFi.status()== WL_CONNECTED){                    //Check WiFi connection status  
        HTTPClient http;                                  //Create new client
        data_to_send = "idUsuario=" + String(id);
        data_to_send += "&wifiSSID=" + ssid;    
        data_to_send += "&wifiPassword=" + password;    

        //Begin new connection to website       
        http.begin("https://k9dispenser.000webhostapp.com/Controlador/placa/registrarDispensador.php");   //Indicate the destination webpage 
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");         //Prepare the header
        
        int response_code = http.POST(data_to_send);                                //Send the POST. This will giveg us a response code
        
        //If the code is higher than 0, it means we received a response
        if(response_code > 0){
          Serial.println("HTTP code " + String(response_code));                     //Print return code
          if(response_code == 200){                                                 //If code is 200, we received a good response and we can read the echo data
            String response_body = http.getString();  
            response_body.trim();                              //Save the data comming from the website
            Serial.print("Server reply: ");                                         //Print data to the monitor for debug
            Serial.println(response_body);

            //Recibe una respuesta sobre si se inserto el id
            if(response_body == "error"){ // quiere decir que hubo un error al intentar insertar el dispensador en la base de datos
              Serial.println("Error al registrar dispensador");
              ESP.restart(); //reinicia como va a estar vacia la memoria volvera a entrar aqui enviando los datos de nuevo
            } else if(response_body != ""){ // actualizar memoria eeprom para agregar el id del dispensador recibido
              Serial.println("ID del dispensador guardado correctamente en la memoria");
              preferences.begin("info", false);
              preferences.putInt("idDispensador", response_body.toInt());
              preferences.end();
              ayuda = 0;
              return;
            } 
          }//End of response_code = 200
        }//END of response_code > 0
        
        else{
        Serial.print("Error sending POST, code: ");
        Serial.println(response_code);
        }
        http.end();                                                                 //End the connection
      }//END of WIFI connected
      else{
        Serial.println("WIFI connection error");
      }
    }
  } while(response_code <= 0); //Seguira ejecutando la funcion apagar hasta que se haya actualizado el valor en la bse de datos correctamente
}

//Le envio informacion a la pagina web con la instruccion de apagar el servo para que cambie el valor en la base de datos
void apagadoAuto(){
  //We make the refresh loop using millis() so we don't have to sue delay();
  response_code = 0;

  do{
    Actual_Millis = millis();
    if(Actual_Millis - Previous_Millis > refresh_time){
      Previous_Millis = Actual_Millis;  
      if(WiFi.status()== WL_CONNECTED){                   //Check WiFi connection status  
        HTTPClient http;                                  //Create new client
        data_to_send = "apagadoAuto=" + String(idDispensador);    
        
        //Begin new connection to website       
        http.begin("https://k9dispenser.000webhostapp.com/Controlador/placa/apagadoAuto.php");   //Indicate the destination webpage 
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");         //Prepare the header
        
        int response_code = http.POST(data_to_send);                                //Send the POST. This will giveg us a response code
        
        //If the code is higher than 0, it means we received a response
        if(response_code > 0){
          Serial.println("HTTP code " + String(response_code));                     //Print return code
    
          if(response_code == 200){                                                 //If code is 200, we received a good response and we can read the echo data
            String response_body = http.getString();                                //Save the data comming from the website
            response_body.trim();     //Quitando los espacios innecesarios al comienzo de la respuesta                        
            Serial.print("Server reply: ");                                         //Print data to the monitor for debug
            Serial.println(response_body);

            //If the received data is LED_is_off, we set LOW the LED pin
            if(response_body == "motor_is_off"){
              Serial.println("Apagado Automatico");
              comidaPlatoActual = balanza.get_units(20);
              Serial.print("Peso: ");
              Serial.print(comidaPlatoActual);
              Serial.println(" g");
            while(comidaPlatoActual <= cantidad){
              comidaPlatoActual = balanza.get_units(20);
              Serial.print("Peso: ");
              Serial.print(comidaPlatoActual);
              Serial.println(" g");

              if(comidaPlatoActual >= cantidad-10){
                servo2.write(120);
                break;
              } else if(comidaPlatoActual <= cantidad - 50){
                servo2.write(75);
              } else if(comidaPlatoActual <= cantidad - 40){
                servo2.write(78);
              } else if(comidaPlatoActual <= cantidad - 30){
                servo2.write(80);
              } else {
                servo2.write(82);
              }
            }

            if (comidaPlatoActual >= cantidad-10){
              servo2.write(120);
            }
              return;
            }        
          }//End of response_code = 200
        } else {//END of response_code > 0
            Serial.println("No se recibio respuesta de la web, apagado automatico de seguridad");
            Serial.print("Error sending POST, code: ");
            Serial.println(response_code);
            estadoMotor = 0;
            servo2.write(120);
        }
        http.end();                                                                 //End the connection
      } else{ //END of WIFI connected
        Serial.println("WIFI connection error");
      } 
    }
  } while(response_code <= 0); //Seguira ejecutando la funcion apagar hasta que se haya actualizado el valor en la bse de datos correctamente
}

//Le envio informacion a la pagina web con la instruccion de apagar el servo para que cambie el valor en la base de datos
void verificarClaveAccesoWifi(){
  response_code = 0;

  do{
    //We make the refresh loop using millis() so we don't have to sue delay();
    Actual_Millis = millis();
    if(Actual_Millis - Previous_Millis > refresh_time){
      Previous_Millis = Actual_Millis;  
      if(WiFi.status()== WL_CONNECTED){                   //Check WiFi connection status  
        HTTPClient http;                                  //Create new client
        data_to_send = "verificarWifi=" + String(idDispensador);
        data_to_send += "&wifiSSID=" + ssid;    
        data_to_send += "&wifiPassword=" + password;      
        
        //Begin new connection to website       
        http.begin("https://k9dispenser.000webhostapp.com/Controlador/placa/comprobarClaveAccesoWifi.php");   //Indicate the destination webpage 
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");         //Prepare the header
        
        int response_code = http.POST(data_to_send);                                //Send the POST. This will giveg us a response code
        
        //If the code is higher than 0, it means we received a response
        if(response_code > 0){
          Serial.println("HTTP code " + String(response_code));                     //Print return code
    
          if(response_code == 200){                                                 //If code is 200, we received a good response and we can read the echo data
            String response_body = http.getString(); 
            response_body.trim();                              //Save the data comming from the website
            Serial.print("Server reply: ");                                         //Print data to the monitor for debug
            Serial.println(response_body);

            // Dividir la respuesta en palabras clave utilizando la coma como delimitador
            String delimiter = ",";
            int idx = 0;
            String keywords[5]; // Puedes ajustar el tamaño del arreglo según tus necesidades
            int numKeywords = 0;

            // Inicio del ciclo while
            while (response_body.length() > 0) {
                // Buscar la posición de la primera coma en la respuesta
                int pos = response_body.indexOf(delimiter);

                // Si se encontró una coma en la respuesta
                if (pos >= 0) {
                    // Obtener la palabra clave desde el inicio de la respuesta hasta la posición de la coma
                    keywords[numKeywords] = response_body.substring(0, pos);

                    // Eliminar la palabra clave y la coma de la respuesta original
                    response_body = response_body.substring(pos + 1);
                } else { // Si no se encontró una coma en la respuesta
                    // La respuesta actual es la última palabra clave, ya que no hay más comas
                    keywords[numKeywords] = response_body;

                    // Vaciar la respuesta para terminar el ciclo while
                    response_body = "";
                }

                // Incrementar el contador de palabras clave
                numKeywords++;

                // Evitar un desbordamiento del arreglo de palabras clave
                if (numKeywords >= 5) {
                    break; // Salir del ciclo while si se alcanza el límite de palabras clave
                }
            }
            // Fin del ciclo while

            if (keywords[0] == "actualizarClaveWifi") { //Si en la primera posicion del arreglo esta "actualizarClaveWifi" quiere decir que habra otros dos valores, el de SSID y password consecutivamente
                Serial.println("Actualizando el wifiSSID y wifiPassword en el memoria NVS");
                //Guardando o reemplazando datos en la namespace config
                preferences.begin("config", false);
                preferences.putString("wifiSSID", keywords[1]); //Por logica ya que asi se enviaron desde php, la posicion 1 del arreglo contiene el ssid
                preferences.putString("wifiPassword", keywords[2]); //Por logica ya que asi se enviaron desde php, la posicion 2 del arreglo contiene el password
                preferences.end();

                delay(500);
                ESP.restart(); // Reiniciando la placa para que se conecte con el nuevo internet
            } else if (keywords[0] == "claveCorrecta") { //Si en la primera posicion del arreglo esta "claveCorrecta" quiere decir no habra mas palabras clave porque el wifi es el mismo en BD y en la memoria de la placa
                Serial.println("Las claves wifi coinciden en la memoria y base de datos");
                ayuda = 0;
                return;
            }           
          }//End of response_code = 200
        }//END of response_code > 0

        else{
        Serial.print("Error sending POST, code: ");
        Serial.println(response_code);
        }
        http.end();                                                                 //End the connection
      }//END of WIFI connected
      else{
        Serial.println("WIFI connection error");
      }
    }
  } while(response_code <= 0); //Seguira ejecutando la funcion para comprobar red wifi hasta que haya una respuesta
}

//Le envio informacion a la pagina web con la instruccion de apagar el servo para que cambie el valor en la base de datos
void consultarCantidadDispensar(){
  response_code = 0;

  do{
    //We make the refresh loop using millis() so we don't have to sue delay();
    Actual_Millis = millis();
    if(Actual_Millis - Previous_Millis > refresh_time){
      Previous_Millis = Actual_Millis;  
      if(WiFi.status()== WL_CONNECTED){                   //Check WiFi connection status  
        HTTPClient http;                                  //Create new client
        data_to_send = "consultarCantidad=" + String(idDispensador);
   
        //Begin new connection to website       
        http.begin("https://k9dispenser.000webhostapp.com/Controlador/placa/cantidadDispensar.php");   //Indicate the destination webpage 
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");         //Prepare the header
        
        int response_code = http.POST(data_to_send);                                //Send the POST. This will giveg us a response code
        
        //If the code is higher than 0, it means we received a response
        if(response_code > 0){
          Serial.println("HTTP code " + String(response_code));                     //Print return code
    
          if(response_code == 200){                                                 //If code is 200, we received a good response and we can read the echo data
            String response_body = http.getString();   
            response_body.trim();     //Quitando los espacios innecesarios al comienzo de la respuesta                        
            //Save the data comming from the website
            Serial.print("Server reply: ");                                         //Print data to the monitor for debug
            Serial.println(response_body);

            if(response_body != "error"){
              Serial.println("Cantidad a dispensar: " + response_body + " gramos");
              cantidad = response_body.toInt();
              return;
            }
           
          }//End of response_code = 200
        }//END of response_code > 0

        else{
        Serial.print("Error sending POST, code: ");
        Serial.println(response_code);
        }
        http.end();                                                                 //End the connection
      }//END of WIFI connected
      else{
        Serial.println("WIFI connection error");
      }
    }
  } while(response_code <= 0); //Seguira ejecutando la funcion para comprobar red wifi hasta que haya una respuesta
}

void setup() {

  Serial.begin(115200);
  balanza.begin(LOADCELL_DOUT_PIN, LOADCELL_SCK_PIN);

 
  if (isNVSEmpty()) { //Si la memoria EEPROM esta vacia 
    Serial.println("NVS vacia. Comenzando modo de configuracion");
    WiFi.softAP(apSSID, apPassword); // Crea una red wifi temporal
    IPAddress apIP(192, 168, 4, 1); // Se crea direccion IP para el punto de acceso
    WiFi.softAPConfig(apIP, apIP, IPAddress(255, 255, 255, 0));  // softAPConfig configura los parametros del wifi temporal, en este caso la Ip y la mascara de subred

    server.on("/obtenerId", HTTP_GET, recibirId);
    server.on("/obtenerId", HTTP_POST, recibirId);
    server.on("/config", HTTP_GET, handleConfigRequest);
    server.on("/config", HTTP_POST, handleConfigRequest);
    server.begin();

    Serial.println("Punto de acceso wifi creado. Abre el configurador de la página");
    ayuda = 1; //Quiere decir que se entro al modo de configuracion y no se puede entrar al loop hasta que se reinicie la plca y vuelva a cambiar a 0

  } else { //Si ya tenemos red wifi guardada en la memoria

    Serial.println("Configuracion de la NVS encontrada. Cargando Configuracion");

    Serial.println("El id del dueño es: " + String(id));
    Serial.println("El id del dispensador es: " + String(idDispensador)); // Convirtiendo una variable entera a string para poder unir las cadenas en el mensaje
    Serial.println("El wifiSSID configurado es: " + ssid);
    Serial.println("El wifiPassword del dueño es: " + password);
    
    delay(10);
    pinMode(LED, OUTPUT);                   //Set pin 2 as OUTPUT
    pinMode(button1, INPUT_PULLDOWN);       //Set pin 13 as INPUT with pulldown

    WiFi.begin(ssid, password);             //Start wifi connection
    Serial.print("Connecting...");
    while (WiFi.status() != WL_CONNECTED) { //Check for the connection
      delay(500);
      Serial.print(".");
    }

    Serial.print("Connected, my IP: ");
    Serial.println(WiFi.localIP());
    Actual_Millis = millis();               //Save time for refresh loop
    Previous_Millis = Actual_Millis; 

    //Si es la primera vez que se entra a este modo de configuracion idDispensador sera igual a 0, la segunda vez ya tendra un idDispensador guardado
    if(idDispensador == 0){
      Serial.println("Enviando credenciales WIFI");
      Previous_Millis = Actual_Millis; 
      ayuda = 1;
      envioCredenciales(); //Enviando credenciales si todavia no se tiene un idDispensador, ya que esta funcion cuando se ejecuta lo devuelve queriendo decir que es la primera vez que se configuro el dispensador
    } else { //Si ya se tiene un idDispensador guardado quiere decir que ya se tiene un wifi configurado y esto se ejecutara solo una vez cuando prenda el dispensador para verificar si no se cambio las claves de acceso wifi en la base de datos desde la web
      Serial.println("Verificando claves de acceso wifi");  
      Previous_Millis = Actual_Millis; 
      ayuda = 1;
      verificarClaveAccesoWifi();
    }

  Serial.print("Lectura del valor del ADC:  ");
  Serial.println(balanza.read());
  Serial.println("No ponga ningun  objeto sobre la balanza");
  Serial.println("Destarando...");
  Serial.println("...");
  balanza.set_scale(445); // Establecemos la escala
  balanza.tare(20);  //El peso actual es considerado Tara.
  
  Serial.println("Listo para pesar");

  servo2.attach(12); 
  servo2.write(120);
  }
  
}

void loop() {  
  if (!isNVSEmpty() && ayuda == 0) { //Si la memoria NVS NO esta vacia(es decir ya se configuro por primera vez la red WIFI) y ya reinicio la placa despues de configurarla(para que salga de modo configuracion y ahora si conecte con las credenciales que puso el usuario)

    /* AGREGAR CODIGO QUE RECIBE LA INFORMACION DE LOS SENSORES, NO OLVIDAR CREAR LAS VARIABLES QUE ESTOY ENVIANDO A PHP JUSTO ABAJO*/
    float comidaPlatoActualDecimal = balanza.get_units(20);
    int comidaPlatoActual = int(comidaPlatoActualDecimal);
    Serial.print("Peso: ");
    Serial.print(comidaPlatoActual);
    Serial.println(" g");

    //We make the refresh loop using millis() so we don't have to sue delay();
    Actual_Millis = millis();
    if(Actual_Millis - Previous_Millis > refresh_time){
      Previous_Millis = Actual_Millis;  
      if(WiFi.status()== WL_CONNECTED){ 
        HTTPClient http;          //Check WiFi connection status                                   //Create new client
        if(toggle_pressed && estadoMotor == 0){                //Si el boton fue presionado y ademas la vairable estado es igual a 0(esto porque la variable estado cambia a 1 cuando ya se esta ejecutando) se puede volver a activar el boton fisico 
          data_to_send = "botonFisicoMotor=" + String(idDispensador);
          data_to_send += "&comidaDisponibleActual=" + String(comidaDisponibleActual);    
          data_to_send += "&comidaPlatoActual=" + String(comidaPlatoActual);  
          toggle_pressed = false;                         //Also equal this variable back to false 
        } else{
          data_to_send = "checar_estadoDispensador=" + String(idDispensador);    //If button wasn't pressed we send text: "check_LED_status"
          data_to_send += "&comidaDisponibleActual=" + String(comidaDisponibleActual);    
          data_to_send += "&comidaPlatoActual=" + String(comidaPlatoActual);
        }
        
        //Begin new connection to website       
        http.begin("https://k9dispenser.000webhostapp.com/Controlador/placa/procesamientoDatos.php");   //Indicate the destination webpage 
        http.addHeader("Content-Type", "application/x-www-form-urlencoded");         //Prepare the header
        
        int response_code = http.POST(data_to_send);                                //Send the POST. This will giveg us a response code
        
        //If the code is higher than 0, it means we received a response
        if(response_code > 0){
          Serial.println("HTTP code " + String(response_code));                     //Print return code
    
          if(response_code == 200){                                                 //If code is 200, we received a good response and we can read the echo data
            String response_body = http.getString();                                //Save the data comming from the website
            response_body.trim();     //Quitando los espacios innecesarios al comienzo de la respuesta                        
            Serial.print("Server reply: ");                                         //Print data to the monitor for debug
            Serial.println(response_body);
            Serial.println("Datos capturados de los sensores enviados");

            if(response_body == "dispensadorFormateado"){ // Quiere decir que se elimino el dispensador, entonces, cuando la web busca algun resultado con el id que enviamos no lo encuentra, lo que quiere decir que no existe
              Serial.println("Dispensador no existente en base de datos. Reiniciando sus configuraciones.");

              //Reemplazando datos en la namespace config por los valores nulos dependiendo el tipo de datos
              preferences.begin("config", false);
              preferences.putString("wifiSSID", "");
              preferences.putString("wifiPassword", "");
              preferences.end();

              //Reemplazando datos en la namespace info por los valores nulos dependiendo el tipo de datos
              preferences.begin("info", false);
              preferences.putString("idUsuario", 0);
              preferences.putString("idDispensador", 0);
              preferences.end();

              ESP.restart(); // Reiniciando placa para que el dispensador entre en modo configuracion al ver que no tiene un wifi configurado
            }

            if(response_body == "motor_is_off"){
              Serial.println("Motor apagado");
            }

            //If the received data is LED_is_on, we set HIGH the LED pin
            if(response_body == "motor_is_on_botonFisico" || response_body == "motor_is_on" || response_body == "motor_is_on_automatizacionEncontrada"){
              if(response_body == "motor_is_on_botonFisico"){
                Serial.println("Motor activado a traves del boton fisico");
              } else if(response_body == "motor_is_on"){
                Serial.println("Motor activado a traves de la web");
              } else if(response_body == "motor_is_on_automatizacionEncontrada"){
                Serial.println("Motor activado a a traves de una automatización programada");
              }

            consultarCantidadDispensar();
     
              comidaPlatoActualDecimal = balanza.get_units(20);
              Serial.print("Peso: ");
              Serial.print(comidaPlatoActualDecimal);
              Serial.println(" g");

              if(comidaPlatoActualDecimal >= cantidad-10){
                servo2.write(120);
              } else if(comidaPlatoActualDecimal <= cantidad - 50){
                servo2.write(75);
              } else if(comidaPlatoActualDecimal <= cantidad - 40){
                servo2.write(78);
              } else if(comidaPlatoActualDecimal <= cantidad - 30){
                servo2.write(80);
              } else {
                servo2.write(82);
              }
              comidaDisponibleActual -= 100;

              apagadoAuto(); //Le envio informacion con la instruccion de apagar para que cambie el valor en la base de datos
            }
          }//End of response_code = 200
        } else { //END of response_code > 0
          Serial.print("Error sending POST, code: ");
          Serial.println(response_code);
        }
        http.end();                                                                 //End the connection
      } else{ //END of WIFI connected
        Serial.println("WIFI connection error");
      }
    }
  }
}



