let password = document.getElementById("password");
let confirmPass = document.getElementById("confpass");
let resetBtn = document.getElementById("resetBtn");
let passwordMsg = document.getElementById("passwordMsg");
let confpassMsg = document.getElementById("confpassMsg");

const urlParams = new URLSearchParams(window.location.search);
const tokenFromUrl = urlParams.get('token');
const emailFromUrl = urlParams.get('email');

function validatePassword() {
    let regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
    if (regex.test(password.value)) {
        password.classList.add("is-valid");
        password.classList.remove("is-invalid");
        passwordMsg.classList.add("d-none");
        passwordMsg.classList.remove("d-block");
        return true;
    } else {
        password.classList.add("is-invalid");
        password.classList.remove("is-valid");
        passwordMsg.classList.add("d-block");
        passwordMsg.classList.remove("d-none");
        return false;
    }
}

function validateConfirmPassword() {
    if (confirmPass.value === password.value && confirmPass.value !== "") {
        confirmPass.classList.add("is-valid");
        confirmPass.classList.remove("is-invalid");
        confpassMsg.classList.add("d-none");
        confpassMsg.classList.remove("d-block");
        return true;
    } else {
        confirmPass.classList.add("is-invalid");
        confirmPass.classList.remove("is-valid");
        confpassMsg.classList.add("d-block");
        confpassMsg.classList.remove("d-none");
        return false;
    }
}

password.addEventListener("input", validatePassword);
confirmPass.addEventListener("input", validateConfirmPassword);

resetBtn.addEventListener("click", async function (e) {
    e.preventDefault();

    let isPasswordValid = validatePassword();
    let isConfPassValid = validateConfirmPassword();

    if (!isPasswordValid || !isConfPassValid) {
        return;
    }

    if (!tokenFromUrl || !emailFromUrl) {
        console.log("Invalid or expired reset link. Please request a new one.");
        return;
    }

    try {
        let response = await fetch(`${Auth.getBaseUrl()}/reset-password`, {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                token: tokenFromUrl,
                email: emailFromUrl,
                password: password.value,
                password_confirmation: confirmPass.value
            })
        });

        let data = await response.json();

        if (response.ok) {
        console.log(data);
        alert("Password updated successfully!");
        window.open("./login.html", "_self");
        } else {
            alert(data.message || "Failed to reset password. Link might be expired.");
        }

    } catch (error) {
        console.log(error);
    }
});