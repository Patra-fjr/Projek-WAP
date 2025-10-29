let timeLeft = 10;
const countdownElement = document.getElementById('countdown');

const countdown = setInterval(() => {
    timeLeft--;
    countdownElement.textContent = timeLeft;

    if (timeLeft <= 0) {
        clearInterval(countdown);
        window.location.href = 'index.php';
    }
}, 1000);