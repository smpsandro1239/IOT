const fs = require('fs');
const path = require('path');
const { createCanvas } = require('canvas');

// Tamanhos de ícones necessários
const sizes = [72, 96, 128, 144, 152, 192, 384, 512];

// Cores
const BLUE = '#2563eb';
const DARK = '#1f2937';
const RED = '#ef4444';
const YELLOW = '#f59e0b';
const GREEN = '#10b981';

// Função para desenhar o ícone do semáforo
function drawTrafficLightIcon(ctx, size) {
  // Fundo azul
  ctx.fillStyle = BLUE;
  ctx.fillRect(0, 0, size, size);

  // Corpo do semáforo
  const bodyWidth = size * 0.16;
  const bodyHeight = size * 0.58;
  const bodyX = (size - bodyWidth) / 2;
  const bodyY = size * 0.21;

  ctx.fillStyle = DARK;
  roundRect(ctx, bodyX, bodyY, bodyWidth, bodyHeight, size * 0.03);

  // Luzes do semáforo
  const lightRadius = size * 0.055;
  const centerX = size / 2;

  // Luz vermelha
  ctx.fillStyle = RED;
  ctx.beginPath();
  ctx.arc(centerX, bodyY + bodyHeight * 0.2, lightRadius, 0, Math.PI * 2);
  ctx.fill();

  // Luz amarela
  ctx.fillStyle = YELLOW;
  ctx.beginPath();
  ctx.arc(centerX, bodyY + bodyHeight * 0.5, lightRadius, 0, Math.PI * 2);
  ctx.fill();

  // Luz verde
  ctx.fillStyle = GREEN;
  ctx.beginPath();
  ctx.arc(centerX, bodyY + bodyHeight * 0.8, lightRadius, 0, Math.PI * 2);
  ctx.fill();
}

// Função auxiliar para desenhar retângulos com cantos arredondados
function roundRect(ctx, x, y, width, height, radius) {
  ctx.beginPath();
  ctx.moveTo(x + radius, y);
  ctx.lineTo(x + width - radius, y);
  ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
  ctx.lineTo(x + width, y + height - radius);
  ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
  ctx.lineTo(x + radius, y + height);
  ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
  ctx.lineTo(x, y + radius);
  ctx.quadraticCurveTo(x, y, x + radius, y);
  ctx.closePath();
  ctx.fill();
}

// Gerar ícones para cada tamanho
sizes.forEach(size => {
  const canvas = createCanvas(size, size);
  const ctx = canvas.getContext('2d');

  drawTrafficLightIcon(ctx, size);

  const buffer = canvas.toBuffer('image/png');
  fs.writeFileSync(path.join(__dirname, `icon-${size}x${size}.png`), buffer);

  console.log(`Ícone ${size}x${size} gerado com sucesso!`);
});

console.log('Todos os ícones foram gerados!');
