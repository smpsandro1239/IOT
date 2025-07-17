# Sistema de Controle de Barreiras IoT

O Sistema de Controle de Barreiras IoT é uma solução completa para gerenciamento de barreiras físicas (cancelas) utilizando tecnologia IoT. O sistema detecta veículos usando LoRa e estima o Ângulo de Chegada (AoA) para determinação de aproximação, permitindo a abertura seletiva de barreiras para veículos autorizados.

## Características Principais

- **Detecção de Veículos:** Detecta veículos usando LoRa e estima o Ângulo de Chegada (AoA) para determinar a direção de aproximação.
- **Autorização por MAC:** Abre a barreira para veículos autorizados com base em seu endereço MAC.
- **Dashboard em Tempo Real:** Um dashboard web exibe o status da barreira, movimentos de veículos e logs do sistema em tempo real.
- **Endpoints API:** Conjunto completo de endpoints API para gerenciar o sistema.
- **Modo de Simulação:** Modo de simulação incluído no frontend para testar o sistema sem hardware físico.
- **Atualizações OTA:** O sistema suporta atualizações de firmware Over-the-Air para o ESP32.
- **Modo Offline:** O frontend suporta operação offline com sincronização quando a conexão é restaurada.
- **PWA:** O frontend é uma Progressive Web App que pode ser instalada em dispositivos móveis.

## Fluxo de Operação

1. Detecção do veículo a 500m
2. Determinação da direção (AoA)
3. Verificação de permissões
4. Abertura da barreira correspondente
5. Bloqueio da barreira oposta (5 minutos)
6. Registro no sistema
