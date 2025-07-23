@echo off
chcp 65001 >nul
cls

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    🚀 TESTE COMPLETO DO SISTEMA FINAL                       ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🚀 Iniciando sistema para teste completo...
echo.

echo [1/3] Iniciando backend...
cd backend
start "Backend API" cmd /k "php artisan serve --host=0.0.0.0 --port=8000"
timeout /t 2 >nul
cd ..

echo [2/3] Iniciando frontend...
cd frontend
start "Frontend Server" cmd /k "php -S localhost:8080"
timeout /t 2 >nul
cd ..

echo [3/3] Aguardando serviços iniciarem...
timeout /t 3 >nul

echo ✅ Sistema iniciado!
echo.
echo 🌐 Abrindo navegador...
start http://localhost:8080

echo.
echo ╔══════════════════════════════════════════════════════════════════════════════╗
echo ║                    📋 TESTE DE IMPORTAR/EXPORTAR                            ║
echo ╚══════════════════════════════════════════════════════════════════════════════╝
echo.

echo 🎯 FUNCIONALIDADES DE IMPORTAR/EXPORTAR:
echo.
echo 1. 📤 TESTE DE EXPORTAÇÃO:
echo.
echo    ✅ PASSO 1 - Adicionar alguns veículos:
echo       • Clique em "MACs Autorizados"
echo       • Adicione: MAC: 24A160123456, Matrícula: AB1234
echo       • Adicione: MAC: 1122334455AA, Matrícula: CD5678
echo       • Adicione: MAC: BBCCDDEE1122, Matrícula: EF9012
echo.
echo    📤 PASSO 2 - Exportar para CSV:
echo       • Na seção "Importar/Exportar"
echo       • Clique em "Baixar MACs"
echo       • Deve baixar arquivo CSV com todos os veículos
echo       • Arquivo deve ter formato: MAC,Matrícula,Autorizado,Último Acesso
echo.
echo 2. 📥 TESTE DE IMPORTAÇÃO:
echo.
echo    📋 PASSO 1 - Ver instruções de formato:
echo       • Clique em "Como formatar o ficheiro?"
echo       • Deve mostrar modal com instruções detalhadas
echo       • Formato: CSV com colunas MAC,Matrícula,Autorizado,Último Acesso
echo.
echo    📥 PASSO 2 - Criar arquivo de teste:
echo       • Crie um arquivo CSV com o seguinte conteúdo:
echo.
echo         MAC,Matrícula,Autorizado,Último Acesso
echo         "99:88:77:66:55:44","GH-90-12","Sim","2025-01-18 15:00:00"
echo         "33:22:11:AA:BB:CC","IJ-34-56","Não","2025-01-18 14:30:00"
echo         "DD:EE:FF:11:22:33","KL-78-90","Sim","2025-01-18 16:15:00"
echo.
echo    📥 PASSO 3 - Importar arquivo:
echo       • Clique em "Selecionar ficheiro"
echo       • Escolha o arquivo CSV criado
echo       • Deve mostrar modal com resultado da importação
echo       • Deve mostrar: Total, Sucessos, Duplicados, Erros
echo.
echo 3. 🔍 TESTE DE FORMATOS FLEXÍVEIS:
echo.
echo    ✅ CENÁRIO A - Formatos sem separadores:
echo       • Crie CSV com: 998877665544,GH9012,Sim
echo       • Deve importar e formatar como: 99:88:77:66:55:44, GH-90-12
echo.
echo    ✅ CENÁRIO B - Formatos com separadores:
echo       • Crie CSV com: 33:22:11:AA:BB:CC,IJ-34-56,Não
echo       • Deve importar corretamente
echo.
echo 4. 🔄 TESTE DE DUPLICATAS NA IMPORTAÇÃO:
echo.
echo    ⚠️  CENÁRIO C - Importar duplicatas:
echo       • Tente importar veículo que já existe
echo       • Sistema deve detectar e contar como duplicado
echo       • Deve atualizar dados existentes
echo.
echo 5. ❌ TESTE DE ERROS:
echo.
echo    ❌ CENÁRIO D - Arquivo inválido:
echo       • Tente importar arquivo não-CSV
echo       • Deve mostrar erro
echo.
echo    ❌ CENÁRIO E - Dados inválidos:
echo       • Crie CSV com MAC inválido: 123,ABC123,Sim
echo       • Deve mostrar erro na importação
echo.

echo ⚠️  IMPORTANTE:
echo    • Sistema: http://localhost:8080
echo    • API: http://localhost:8000
echo    • Pressione Ctrl+C nas janelas para parar
echo.

echo 📊 FUNCIONALIDADES A VERIFICAR:
echo    ✅ Exportação para CSV funcional
echo    ✅ Importação de CSV funcional
echo    ✅ Modal de instruções de formato
echo    ✅ Modal de resultados de importação
echo    ✅ Validação de formatos na importação
echo    ✅ Detecção de duplicatas na importação
echo    ✅ Tratamento de erros na importação
echo    ✅ Formatos flexíveis (com/sem separadores)
echo    ✅ Pesquisa flexível após importação
echo    ✅ Último acesso atualizado
echo    ✅ Persistência em localStorage
echo.

echo 🎯 RESULTADOS ESPERADOS:
echo    ✅ Arquivo CSV exportado com dados corretos
echo    ✅ Importação bem-sucedida com feedback detalhado
echo    ✅ Formatos normalizados automaticamente
echo    ✅ Duplicatas detectadas e tratadas
echo    ✅ Erros reportados claramente
echo    ✅ Dados persistidos entre sessões
echo    ✅ Pesquisa funciona com dados importados
echo.

echo 💡 EXEMPLO DE ARQUIVO CSV PARA TESTE:
echo.
echo MAC,Matrícula,Autorizado,Último Acesso
echo "24:A1:60:12:34:56","AB-12-34","Sim","2025-01-18 10:30:00"
echo "11:22:33:44:55:AA","CD-56-78","Sim","2025-01-18 09:15:00"
echo "BB:CC:DD:EE:11:22","EF-90-12","Não","2025-01-18 11:45:00"
echo "99:88:77:66:55:44","GH-90-12","Sim","2025-01-18 15:00:00"
echo "33:22:11:AA:BB:CC","IJ-34-56","Não","2025-01-18 14:30:00"
echo.

pause