@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🚀 ENVIAR CÓDIGO - PLATFORMIO                            ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🔧 Enviando código para ESP32 usando PlatformIO...
echo.

echo ✅ CREDENCIAIS CONFIGURADAS:
echo    WiFi: Meo-9C27F0
echo    Senha: BB3F5435FF
echo    IP Servidor: 192.168.1.90
echo.

echo [1/4] Verificando PlatformIO...
where pio >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PlatformIO não encontrado
    echo 📥 Instalando PlatformIO...
    pip install platformio
    if %errorlevel% neq 0 (
        echo ❌ Erro ao instalar PlatformIO
        echo 💡 Instale manualmente: https://platformio.org/install
        pause
        exit /b 1
    )
) else (
    echo ✅ PlatformIO encontrado
)

echo.
echo [2/4] Copiando firmware atualizado...
copy "base\src\main_updated.cpp" "base\src\main.cpp" >nul
echo ✅ Firmware copiado

echo.
echo [3/4] Compilando e enviando...
echo 🔌 CONECTE A PLACA ESP32 VIA USB AGORA
echo.
pause

cd base
echo 📦 Compilando...
pio run

if %errorlevel% neq 0 (
    echo ❌ Erro na compilação!
    echo 📚 Instalando dependências...
    pio lib install
    pio run
)

echo.
echo 📤 Enviando para ESP32...
pio run -t upload

if %errorlevel% neq 0 (
    echo ❌ Erro no upload!
    echo 🔧 Soluções:
    echo    1. Verifique se a placa está conectada
    echo    2. Pressione o botão BOOT durante o upload
    echo    3. Tente: pio run -t upload --upload-port COM3
    pause
    exit /b 1
)

echo ✅ Upload concluído!

echo.
echo 📡 Abrindo Serial Monitor...
pio device monitor

cd ..

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🎉 CÓDIGO ENVIADO COM SUCESSO!                           ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

pause