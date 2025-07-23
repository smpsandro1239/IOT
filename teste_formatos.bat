@echo off
chcp 65001 >nul
echo ===================================================
echo    TESTE DE FORMATOS DE ENTRADA - BARREIRAS IOT
echo    Validação e Padronização Automática
echo ===================================================
echo.

echo 🔍 Testando novos formatos de entrada...
echo.

echo ✅ FORMATOS ACEITOS:
echo.
echo 📱 MAC ADDRESS:
echo    ✅ Formato compacto: 1234567890aa
echo    ✅ Formato padrão: 12:34:56:78:90:aa
echo    ✅ Formato misto: 12-34-56-78-90-aa
echo    ✅ Case insensitive: 1234567890AA ou 1234567890aa
echo.
echo 🚗 MATRÍCULA:
echo    ✅ Formato compacto: AA1212
echo    ✅ Formato padrão: AA-12-12
echo    ✅ Case insensitive: aa1212 ou AA1212
echo.
echo 💾 ARMAZENAMENTO PADRONIZADO:
echo    ✅ MAC: Sempre salvo como 12:34:56:78:90:AA (maiúsculas com dois pontos)
echo    ✅ Matrícula: Sempre salva como AA-12-12 (maiúsculas com hífens)
echo.
echo 🧪 CENÁRIOS DE TESTE:
echo.
echo 1️⃣ TESTE MAC FORMATO COMPACTO:
echo    Entrada: 1234567890aa
echo    Resultado esperado: 12:34:56:78:90:AA
echo    Status: ✅ Deve aceitar e formatar automaticamente
echo.
echo 2️⃣ TESTE MAC FORMATO PADRÃO:
echo    Entrada: 12:34:56:78:90:aa
echo    Resultado esperado: 12:34:56:78:90:AA
echo    Status: ✅ Deve aceitar e converter para maiúsculas
echo.
echo 3️⃣ TESTE MATRÍCULA FORMATO COMPACTO:
echo    Entrada: aa1212
echo    Resultado esperado: AA-12-12
echo    Status: ✅ Deve aceitar e formatar automaticamente
echo.
echo 4️⃣ TESTE MATRÍCULA FORMATO PADRÃO:
echo    Entrada: AA-12-12
echo    Resultado esperado: AA-12-12
echo    Status: ✅ Deve aceitar sem alterações
echo.
echo 5️⃣ TESTE COMBINADO:
echo    MAC: 9988776655aa + Matrícula: bb3434
echo    Resultado esperado: 99:88:77:66:55:AA + BB-34-34
echo    Status: ✅ Ambos devem ser formatados corretamente
echo.
echo 6️⃣ TESTE FORMATOS INVÁLIDOS:
echo    MAC inválido: 12345 (muito curto)
echo    MAC inválido: 1234567890xyz (caracteres inválidos)
echo    Matrícula inválida: A1 (muito curta)
echo    Matrícula inválida: ABCDEFG (muito longa)
echo    Status: ❌ Deve mostrar erro de validação
echo.
echo 🎯 VALIDAÇÕES IMPLEMENTADAS:
echo.
echo 🔧 MAC ADDRESS:
echo    ✅ Exatamente 12 caracteres hexadecimais
echo    ✅ Aceita separadores (: ou -) ou sem separadores
echo    ✅ Case insensitive na entrada
echo    ✅ Conversão automática para maiúsculas
echo    ✅ Formatação automática com dois pontos
echo.
echo 🔧 MATRÍCULA:
echo    ✅ Exatamente 6 caracteres alfanuméricos
echo    ✅ Formato português: 2 letras + 4 números OU 2 letras + 2 números + 2 letras
echo    ✅ Aceita com ou sem hífens
echo    ✅ Case insensitive na entrada
echo    ✅ Conversão automática para maiúsculas
echo    ✅ Formatação automática com hífens (XX-XX-XX)
echo.
echo 💡 EXEMPLOS PRÁTICOS DE TESTE:
echo.
echo Teste 1 - Entrada simples:
echo    MAC: 24a160123456
echo    Matrícula: ab1234
echo    Resultado: 24:A1:60:12:34:56 + AB-12-34
echo.
echo Teste 2 - Entrada com separadores:
echo    MAC: 24-a1-60-12-34-56
echo    Matrícula: AB-12-34
echo    Resultado: 24:A1:60:12:34:56 + AB-12-34
echo.
echo Teste 3 - Entrada mista:
echo    MAC: 24:A1:60:12:34:56
echo    Matrícula: cd5678
echo    Resultado: 24:A1:60:12:34:56 + CD-56-78
echo.
echo 🚀 COMO TESTAR:
echo.
echo 1. Acesse: http://localhost:8080
echo 2. Faça login: admin@example.com / password
echo 3. Vá para a seção "Adicionar Veículo"
echo 4. Teste cada formato listado acima
echo 5. Observe as mensagens de validação
echo 6. Verifique se os dados são salvos no formato correto
echo 7. Confirme na seção de pesquisa se aparecem formatados
echo.
echo ⚠️  MENSAGENS DE ERRO ESPERADAS:
echo.
echo ❌ MAC muito curto: "MAC deve ter exatamente 12 caracteres hexadecimais"
echo ❌ MAC com caracteres inválidos: "Formato de MAC inválido"
echo ❌ Matrícula muito curta: "Matrícula deve ter exatamente 6 caracteres"
echo ❌ Matrícula inválida: "Formato de matrícula inválido"
echo.
echo 📊 DADOS DE TESTE SUGERIDOS:
echo.
echo Válidos:
echo    MAC: 1234567890ab, 12:34:56:78:90:ab, 12-34-56-78-90-ab
echo    Matrícula: aa1234, AA-12-34, bb5678, BB-56-78
echo.
echo Inválidos:
echo    MAC: 12345, 1234567890xyz, 12:34:56:78
echo    Matrícula: A1, ABCDEFGH, 123456
echo.
echo ===================================================
echo    ✅ TESTE PRONTO PARA EXECUÇÃO!
echo ===================================================
echo.
echo 🚀 Para iniciar o sistema:
echo    iniciar_sistema_otimizado.bat
echo.
echo 🌐 Acesse: http://localhost:8080
echo.
echo 📝 DICA: Abra o console do navegador (F12) para ver logs detalhados
echo         dos processos de validação e formatação
echo.
pause