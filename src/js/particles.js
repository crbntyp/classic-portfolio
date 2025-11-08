/**
 * Particle Background Animation
 * Creates floating particles across the canvas background
 */

document.addEventListener('DOMContentLoaded', function() {
  const canvas = document.getElementById('particle-canvas');
  if (!canvas) return;

  const ctx = canvas.getContext('2d');

  let particles = [];
  const particleCount = 50;
  const colors = ['rgba(255, 255, 255, 0.3)', 'rgba(255, 255, 255, 0.2)', 'rgba(255, 255, 255, 0.15)', 'rgba(255, 255, 255, 0.25)'];

  // Set canvas size
  function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  }

  resizeCanvas();
  window.addEventListener('resize', resizeCanvas);

  // Create particles
  function createParticles() {
    particles = [];
    for (let i = 0; i < particleCount; i++) {
      particles.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        radius: Math.random() * 2 + 0.5,
        color: colors[Math.floor(Math.random() * colors.length)],
        speed: Math.random() * 0.3 + 0.05,
        direction: Math.random() * Math.PI * 2,
        opacity: Math.random() * 0.4 + 0.1
      });
    }
  }

  createParticles();

  // Animate particles
  function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    particles.forEach(particle => {
      // Move particles
      particle.x += Math.cos(particle.direction) * particle.speed;
      particle.y += Math.sin(particle.direction) * particle.speed;

      // Wrap around canvas
      if (particle.x < 0) particle.x = canvas.width;
      if (particle.x > canvas.width) particle.x = 0;
      if (particle.y < 0) particle.y = canvas.height;
      if (particle.y > canvas.height) particle.y = 0;

      // Draw particle
      ctx.beginPath();
      ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
      ctx.fillStyle = particle.color;
      ctx.globalAlpha = particle.opacity;
      ctx.fill();
      ctx.globalAlpha = 1;
    });

    requestAnimationFrame(animate);
  }

  animate();
});
