let email = document.getElementById("email")
let password = document.getElementById("password")
let loginBtn = document.getElementById("login")
let emailMsg = document.getElementById("emailMsg")
let passwordMsg = document.getElementById("passwordMsg")

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
    if (password.value !== "") {
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

email.addEventListener("input", validateEmail);
password.addEventListener("input", validatePassword);

loginBtn.addEventListener("click", async function (e) {
    e.preventDefault()

    let isEmailValid = validateEmail();
    let isPasswordValid = validatePassword();

    if (!isEmailValid || !isPasswordValid) {
        return;
    }

    try {
        let response = await fetch(`${Auth.getBaseUrl()}/login`, {
            method: "POST",
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "ngrok-skip-browser-warning": "true"
            },
            body: JSON.stringify({
                email: email.value,
                password: password.value
            })
        })

        let data = await response.json();
        
        if (response.ok && data.data && data.data.token) {
            localStorage.setItem("token", data.data.token);
            console.log("Login Success:", data);
            
            // Fetch user profile to check role
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
            alert(data.message || "Invalid credentials");
        }

    } catch (error) {
        console.log(error);
    }
})