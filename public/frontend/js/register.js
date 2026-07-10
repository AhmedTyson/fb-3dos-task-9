let fullName = document.getElementById("name")
let email = document.getElementById("email")
let password = document.getElementById("password")
let confirmPass = document.getElementById("confpass")
let registerBtn = document.getElementById("register")
let nameMsg = document.getElementById("nameMsg")
let emailMsg = document.getElementById("emailMsg")
let passwordMsg = document.getElementById("passwordMsg")
let confpassMsg = document.getElementById("confpassMsg")

function validateName() {
    let regex = /^[a-zA-Z0-9 ]{3,20}$/;
    if (regex.test(fullName.value)) {
        fullName.classList.add("is-valid");
        fullName.classList.remove("is-invalid");
        nameMsg.classList.add("d-none");
        nameMsg.classList.remove("d-block");
        return true;
    } else {
        fullName.classList.add("is-invalid");
        fullName.classList.remove("is-valid");
        nameMsg.classList.add("d-block");
        nameMsg.classList.remove("d-none");
        return false;
    }
}

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

fullName.addEventListener("input", validateName);
email.addEventListener("input", validateEmail);
password.addEventListener("input", validatePassword);
confirmPass.addEventListener("input", validateConfirmPassword);

registerBtn.addEventListener("click", async function (e) {
    e.preventDefault()
    let isNameValid = validateName();
    let isEmailValid = validateEmail();
    let isPasswordValid = validatePassword();
    let isConfPassValid = validateConfirmPassword();

    if (!isNameValid || !isEmailValid || !isPasswordValid || !isConfPassValid) {
        return;
    }
    try {
        let response = await fetch(`${Auth.getBaseUrl()}/register`, {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "ngrok-skip-browser-warning": "true"
            },
            body: JSON.stringify({
                name: fullName.value,
                email: email.value,
                password: password.value,
                password_confirmation: confirmPass.value
            })
        })

        let data = await response.json();
        
        if (response.ok) {
            console.log("Registration Success, logging in...");
            await autoLogin(email.value, password.value);
        } else {
            alert(data.message || "Registration failed");
        }
    } catch (error) {
        console.log("Network Error:", error);
    }
})

async function autoLogin(userEmail, userPassword) {
    try {
        let response = await fetch(`${Auth.getBaseUrl()}/login`, {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "ngrok-skip-browser-warning": "true"
            },
            body: JSON.stringify({
                email: userEmail,
                password: userPassword
            })
        });
        
        let data = await response.json();
        if (response.ok && data.data && data.data.token) {
            localStorage.setItem("token", data.data.token);
            // Check role for redirect
            let userResp = await fetch(`${Auth.getBaseUrl()}/me`, {
                headers: { "Accept": "application/json", "Authorization": `Bearer ${data.data.token}` }
            });
            let userData = await userResp.json();
            if (userData.data) {
                localStorage.setItem("user", JSON.stringify(userData.data));
            }
            if (userData.data?.role === 'admin') {
                window.open("../admin/index.html", "_self");
            } else {
                window.open("./home.html", "_self");
            }
        } else {
            window.open("./login.html", "_self");
        }
    } catch (error) {
        console.log("Auto Login Error:", error);
        window.open("./login.html", "_self");
    }
}