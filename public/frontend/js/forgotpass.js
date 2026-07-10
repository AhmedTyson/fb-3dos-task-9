let email = document.getElementById("email")
let sendLink = document.getElementById("forget")
let emailMsg = document.getElementById("emailMsg");

function validateEmail() {
    let regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (regex.test(email.value)) {
        email.classList.add("is-valid");
        email.classList.remove("is-invalid");
        emailMsg.classList.add("d-none");
        emailMsg.classList.remove("d-block");
        return true;
    } else {
        email.classList.add("is-invalid");
        email.classList.remove("is-valid");
        emailMsg.classList.add("d-block");
        emailMsg.classList.remove("d-none");
        return false;
    }
}

email.addEventListener("input", validateEmail);

sendLink.addEventListener("click", async function (e) {
    e.preventDefault()
    let isEmailValid = validateEmail();
    if (!isEmailValid) {
        return;
    }

    sendLink.disabled = true;
    sendLink.innerText = "Sending...";

    try {
        let response = await fetch(`${Auth.getBaseUrl()}/forgot-password`, {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                email: email.value
            })
        })

        let data = await response.json();
        if (response.ok) {
            console.log(data);
            alert("Reset link has been sent to your email successfully!");
            email.value = "";
            email.classList.remove("is-valid");
        } else {
            alert(data.message || "This email is not registered.");
        }

    } catch (error) {
        console.log(error);
    } finally {
        sendLink.disabled = false;
        sendLink.innerText = "Send Reset Link";
    }
});