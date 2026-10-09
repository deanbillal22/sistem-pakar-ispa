// ===============================
// SHOW / HIDE PASSWORD
// ===============================

const passwordInput = document.getElementById("password");

const togglePassword = document.querySelector(".toggle-password");

if (togglePassword && passwordInput) {
  togglePassword.addEventListener("click", function () {
    const icon = this.querySelector("i");

    if (passwordInput.type === "password") {
      passwordInput.type = "text";

      icon.classList.remove("bi-eye");

      icon.classList.add("bi-eye-slash");
    } else {
      passwordInput.type = "password";

      icon.classList.remove("bi-eye-slash");

      icon.classList.add("bi-eye");
    }
  });
}

// ===============================
// INPUT FOCUS EFFECT
// ===============================

const inputs = document.querySelectorAll(".form-control");

inputs.forEach((input) => {
  input.addEventListener("focus", function () {
    this.parentElement.classList.add("active");
  });

  input.addEventListener("blur", function () {
    this.parentElement.classList.remove("active");
  });
});

// ===============================
// BUTTON EFFECT
// ===============================

const loginButton = document.querySelector(".btn-login");

if (loginButton) {
  loginButton.addEventListener("click", function () {
    this.style.transform = "scale(.98)";

    setTimeout(() => {
      this.style.transform = "scale(1)";
    }, 120);
  });
}

// ===============================
// ENTER SUBMIT
// ===============================

document.addEventListener("keypress", function (e) {
  if (e.key === "Enter") {
    document.querySelector("form").submit();
  }
});

// ===============================
// LOGO ANIMATION
// ===============================

const logo = document.querySelector(".logo");

if (logo) {
  logo.addEventListener("mouseenter", () => {
    logo.style.transform = "rotate(8deg) scale(1.05)";
  });

  logo.addEventListener("mouseleave", () => {
    logo.style.transform = "rotate(0deg) scale(1)";
  });
}
