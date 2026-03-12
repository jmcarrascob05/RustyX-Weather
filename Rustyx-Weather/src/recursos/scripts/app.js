// Animación de entrada para los valores de las tarjetas
document.querySelectorAll('.tarjeta-dato .valor, .tarjeta-principal .temperatura').forEach(el => {
    el.style.opacity = 0;
    el.style.transform = 'translateY(6px)';
    el.style.transition = 'opacity .35s ease, transform .35s ease';
    setTimeout(() => {
        el.style.opacity = 1;
        el.style.transform = 'none';
    }, 80);
});
