/**
 * ESP32 Arduino Code - Bi-Directional Sync Example
 * Update your ESP32 code to send pump_status and handle JSON responses
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// Pin Definitions
#define PUMP_PIN 25        // Pin untuk relay pompa
#define pH_SENSOR_PIN 34   // Pin sensor pH
#define LEVEL_SENSOR_PIN 35 // Pin sensor level air

// Server Configuration
const char* server_url = "http://your-server.com/input.php";
const char* wifi_ssid = "YOUR_WIFI_SSID";
const char* wifi_password = "YOUR_WIFI_PASSWORD";

// Device ID
const char* deviceId = "ESP32-001";

void setup() {
  Serial.begin(115200);
  
  // Setup pins
  pinMode(PUMP_PIN, OUTPUT);
  digitalWrite(PUMP_PIN, LOW); // Pompa mati saat startup
  
  // Connect to WiFi
  WiFi.begin(wifi_ssid, wifi_password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Connected!");
}

void loop() {
  // Read sensors
  float phValue = readPH();
  float batteryLevel = readBattery();
  float waterLevel = readWaterLevel();
  int signalStrength = WiFi.RSSI();
  
  // Read current pump status (CRITICAL untuk bi-directional sync!)
  bool pumpIsOn = digitalRead(PUMP_PIN);
  
  // Send data to server
  String command = sendDataToServer(phValue, batteryLevel, waterLevel, signalStrength, pumpIsOn);
  
  // Process command from server
  if (command == "ON") {
    digitalWrite(PUMP_PIN, HIGH);
    Serial.println("Pompa NYALA (from server)");
  } else if (command == "OFF") {
    digitalWrite(PUMP_PIN, LOW);
    Serial.println("Pompa MATI (from server)");
  }
  
  delay(10000); // Kirim data setiap 10 detik
}

String sendDataToServer(float ph, float battery, float level, int signal, bool pumpStatus) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi not connected!");
    return "ERROR";
  }
  
  HTTPClient http;
  http.begin(server_url);
  http.addHeader("Content-Type", "application/json");
  
  // Buat JSON payload
  DynamicJsonDocument doc(512);
  doc["ph"] = ph;
  doc["battery"] = battery;
  doc["level"] = level;
  doc["signal"] = signal;
  doc["deviceId"] = deviceId; // defined in global scope
  doc["pump_status"] = pumpStatus; // IMPORTANT!
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  Serial.println("Sending: " + jsonString);
  
  int httpCode = http.POST(jsonString);
  String finalCommand = "ERROR";
  
  if (httpCode > 0) {
    String response = http.getString();
    Serial.println("Response: " + response);
    
    // Robust Parsing Logic (User Recommendation)
    response.toUpperCase(); // Case insensitive
    
    // Check for explicit JSON field first if possible, or string search
    DynamicJsonDocument responseDoc(512);
    DeserializationError error = deserializeJson(responseDoc, response);
    
    if (!error) {
       String cmd = responseDoc["command"].as<String>();
       finalCommand = cmd;
    } else {
       // Fallback text search if JSON parsing fails
       if (response.indexOf("\"COMMAND\":\"ON\"") >= 0 || response.indexOf("ON") >= 0) {
           if (response.indexOf("OFF") == -1) finalCommand = "ON";
       }
       if (response.indexOf("\"COMMAND\":\"OFF\"") >= 0 || response.indexOf("OFF") >= 0) {
           finalCommand = "OFF";
       }
    }
    
  } else {
    Serial.println("HTTP Error: " + String(httpCode));
  }
  
  http.end();
  return finalCommand;
}

// In loop(): Update hardware state immediately based on command
/*
  // Process command from server
  if (command == "ON") {
    digitalWrite(PUMP_PIN, LOW); // Active Low Relay (Check your hardware!)
    // Update pump status variable immediately for next loop
    // pumpStatus = true; 
  } else if (command == "OFF") {
    digitalWrite(PUMP_PIN, HIGH);
    // pumpStatus = false;
  }
*/

// Helper functions untuk baca sensor
float readPH() {
  int analogValue = analogRead(pH_SENSOR_PIN);
  // Konversi analog ke pH (sesuaikan dengan kalibrasi sensor Anda)
  float voltage = analogValue * (3.3 / 4095.0);
  float phValue = 3.5 * voltage; // Example formula, adjust based on your sensor
  return phValue;
}

float readBattery() {
  // Baca voltage baterai (sesuaikan dengan hardware Anda)
  // Example: 0-100%
  return 85.5;
}

float readWaterLevel() {
  int analogValue = analogRead(LEVEL_SENSOR_PIN);
  // Konversi ke cm atau %
  float level = (analogValue / 4095.0) * 100.0;
  return level;
}
