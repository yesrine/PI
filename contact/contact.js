// contact.js

document.getElementById("contactForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const subject = document.getElementById("subject").value.trim();
    const message = document.getElementById("message").value.trim();
    const status = document.getElementById("form-status");

    if (!name || !email || !subject || !message) {
        status.style.color = "red";
        status.innerText = "Veuillez remplir tous les champs.";
        return;
    }

    // Simule l’envoi (exemple)
    setTimeout(() => {
        status.style.color = "green";
        status.innerText = "Votre message a bien été envoyé. Merci !";
        document.getElementById("contactForm").reset();
    }, 1000);
});