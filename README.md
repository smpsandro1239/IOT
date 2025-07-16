# IoT Barrier Control System

This project is a complete IoT-based Barrier Control System using Laravel for the backend, a static HTML/JavaScript frontend with Tailwind CSS, and an ESP32 with LoRa for the base board.

## Features

-   **Vehicle Detection:** Detects vehicles using LoRa and estimates the Angle of Arrival (AoA) to determine the direction of approach.
-   **MAC-based Authorization:** Opens the barrier for authorized vehicles based on their MAC address.
-   **Real-time Dashboard:** A web-based dashboard displays real-time barrier status, vehicle movements, and system logs.
-   **API Endpoints:** A comprehensive set of API endpoints for managing the system, including adding authorized MAC addresses, logging telemetry data, and checking firmware updates.
-   **Simulation Mode:** A simulation mode is included in the frontend for testing the system without physical hardware.
-   **OTA Updates:** The system supports Over-the-Air (OTA) firmware updates for the ESP32.

## System Architecture

The system is composed of three main components:

1.  **Backend (Laravel):** A Laravel application that provides the API endpoints for the system.
2.  **Frontend (HTML/JavaScript):** A static HTML/JavaScript application that provides the web-based dashboard.
3.  **ESP32 Firmware:** An Arduino sketch that runs on the ESP32 and controls the barrier.

## Setup Instructions

### Prerequisites

-   [Laragon](https://laragon.org/download/) (or any other local development environment for PHP)
-   [MySQL](https://www.mysql.com/downloads/)
-   [Node.js](https://nodejs.org/en/download/)
-   [Arduino IDE](https://www.arduino.cc/en/software)
-   [ESP32 Board Support for Arduino IDE](https://docs.espressif.com/projects/arduino-esp32/en/latest/installing.html)
-   Libraries for ESP32: `LoRa`, `WiFi`, `HTTPClient`

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/iot-barrier-control.git
cd iot-barrier-control
```

### 2. Backend Setup

```bash
cd backend
composer install
cp .env.example .env
```

After copying the `.env.example` file, you need to update the `.env` file with your database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_barrier_control
DB_USERNAME=root
DB_PASSWORD=your_password
```

Then, run the following commands to generate the application key and run the database migrations:

```bash
php artisan key:generate
php artisan migrate --seed
```

Finally, start the backend server and the WebSocket server:

```bash
php artisan serve
php artisan websockets:serve
```

### 3. Frontend Setup

```bash
cd frontend
npm install
npm install http-server -g
http-server
```

The frontend will be available at `http://127.0.0.1:8080`.

### 4. ESP32 Setup

1.  Open the `auto/src/BarrierControl.ino` file in the Arduino IDE.
2.  Update the WiFi credentials and the server URL in the file:

    ```c++
    const char* ssid = "your_wifi_ssid";
    const char* password = "your_wifi_password";
    const char* serverUrl = "http://your-laravel-app-url/api/v1/access-logs";
    const char* authUrl = "http://your-laravel-app-url/api/v1/macs-autorizados/authorize";
    ```

3.  Flash the firmware to your ESP32 board.

### 5. Firewall

If you are using a firewall, you need to open port `6001` for WebSocket access:

```bash
netsh advfirewall firewall add rule name="WebSocket 6001" dir=in action=allow protocol=TCP localport=6001
```

## API Testing

You can use the following `curl` commands to test the API endpoints:

### Add a MAC address

```bash
curl -X POST http://127.0.0.1:8000/api/v1/macs-autorizados -H "Content-Type: application/json" -d '{"mac":"24A160123456","placa":"ABC123"}'
```

### Test telemetry

```bash
curl -X POST http://127.0.0.1:8000/api/v1/access-logs -H "Content-Type: application/json" -d '{"mac":"24A160123456","direcao":"NS","datahora":"2025-07-16 01:49:00","status":"AUTORIZADO"}'
```

### Get latest status

```bash
curl http://127.0.0.1:8000/api/v1/status/latest
```

### Get all authorized MAC addresses

```bash
curl http://127.0.0.1:8000/api/v1/macs-autorizados
```

### Check MAC authorization

```bash
curl http://127.0.0.1:8000/api/v1/macs-autorizados/authorize?mac=24A160123456
```
