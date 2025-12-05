/** @format */

// ==========================================
// KAFE LATTE - REGISTER PAGE ENHANCED JAVASCRIPT
// ==========================================

document.addEventListener("DOMContentLoaded", function () {
  // ==========================================
  // FORM ELEMENTS
  // ==========================================
  const form = document.querySelector(".auth-form");
  const fullNameInput = document.getElementById("full_name");
  const emailInput = document.getElementById("email");
  const phoneInput = document.getElementById("phone");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirm_password");

  // ==========================================
  // PASSWORD TOGGLE ENHANCED
  // ==========================================
  window.togglePassword = function (inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector(".toggle-password");
    const icon = button.querySelector("i");

    if (input.type === "password") {
      input.type = "text";
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
      button.style.color = "var(--secondary-color)";
    } else {
      input.type = "password";
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
      button.style.color = "var(--text-light)";
    }

    // Animation
    button.style.transform = "scale(1.1)";
    setTimeout(() => {
      button.style.transform = "scale(1)";
    }, 200);
  };

  // ==========================================
  // REAL-TIME VALIDATION
  // ==========================================

  // Full Name Validation
  if (fullNameInput) {
    fullNameInput.addEventListener("input", function () {
      validateFullName(this);
    });

    fullNameInput.addEventListener("blur", function () {
      validateFullName(this);
    });
  }

  function validateFullName(input) {
    const name = input.value.trim();
    removeValidationMessage(input);

    if (name === "") {
      input.classList.remove("valid", "invalid");
      return;
    }

    if (name.length >= 3) {
      input.classList.remove("invalid");
      input.classList.add("valid");
      showValidationMessage(input, "Nama valid", "success");
    } else {
      input.classList.remove("valid");
      input.classList.add("invalid");
      showValidationMessage(input, "Nama minimal 3 karakter", "error");
    }
  }

  // Email Validation
  if (emailInput) {
    emailInput.addEventListener("input", function () {
      validateEmail(this);
    });

    emailInput.addEventListener("blur", function () {
      validateEmail(this);
    });
  }

  function validateEmail(input) {
    const email = input.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    removeValidationMessage(input);

    if (email === "") {
      input.classList.remove("valid", "invalid");
      return;
    }

    if (emailRegex.test(email)) {
      input.classList.remove("invalid");
      input.classList.add("valid");
      showValidationMessage(input, "Email valid", "success");
    } else {
      input.classList.remove("valid");
      input.classList.add("invalid");
      showValidationMessage(input, "Format email tidak valid", "error");
    }
  }

  // Phone Validation
  if (phoneInput) {
    phoneInput.addEventListener("input", function () {
      // Only allow numbers
      this.value = this.value.replace(/[^0-9+\-\s]/g, "");
      validatePhone(this);
    });

    phoneInput.addEventListener("blur", function () {
      validatePhone(this);
    });
  }

  function validatePhone(input) {
    const phone = input.value.trim();
    removeValidationMessage(input);

    if (phone === "") {
      input.classList.remove("valid", "invalid");
      return;
    }

    // Indonesian phone number pattern
    const phoneRegex = /^(\+62|62|0)[0-9]{9,12}$/;

    if (phoneRegex.test(phone.replace(/[\s\-]/g, ""))) {
      input.classList.remove("invalid");
      input.classList.add("valid");
      showValidationMessage(input, "Nomor telepon valid", "success");
    } else {
      input.classList.remove("valid");
      input.classList.add("invalid");
      showValidationMessage(input, "Format nomor tidak valid", "error");
    }
  }

  // Password Validation
  if (passwordInput) {
    passwordInput.addEventListener("input", function () {
      validatePassword(this);
      if (confirmPasswordInput.value !== "") {
        validateConfirmPassword(confirmPasswordInput);
      }
    });

    passwordInput.addEventListener("blur", function () {
      validatePassword(this);
    });
  }

  function validatePassword(input) {
    const password = input.value;
    removeValidationMessage(input);

    if (password === "") {
      input.classList.remove("valid", "invalid");
      return;
    }

    // Password strength check
    let strength = 0;
    let messages = [];

    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;

    if (password.length < 6) {
      input.classList.remove("valid");
      input.classList.add("invalid");
      showValidationMessage(input, "Password minimal 6 karakter", "error");
    } else {
      input.classList.remove("invalid");
      input.classList.add("valid");

      // Show strength indicator
      let strengthText = "";
      if (strength <= 2) {
        strengthText = "Password lemah 😟";
      } else if (strength <= 3) {
        strengthText = "Password cukup kuat 😊";
      } else {
        strengthText = "Password kuat! 🔒";
      }

      showValidationMessage(input, strengthText, "success");
    }

    // Update strength bar
    updatePasswordStrength(strength);
  }

  // Confirm Password Validation
  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener("input", function () {
      validateConfirmPassword(this);
    });

    confirmPasswordInput.addEventListener("blur", function () {
      validateConfirmPassword(this);
    });
  }

  function validateConfirmPassword(input) {
    const confirmPassword = input.value;
    const password = passwordInput.value;

    removeValidationMessage(input);

    if (confirmPassword === "") {
      input.classList.remove("valid", "invalid");
      return;
    }

    if (confirmPassword === password) {
      input.classList.remove("invalid");
      input.classList.add("valid");
      showValidationMessage(input, "Password cocok! ✓", "success");
    } else {
      input.classList.remove("valid");
      input.classList.add("invalid");
      showValidationMessage(input, "Password tidak cocok", "error");
    }
  }

  // ==========================================
  // PASSWORD STRENGTH INDICATOR
  // ==========================================
  function updatePasswordStrength(strength) {
    const strengthBar = document.querySelector(".password-strength-bar");
    if (!strengthBar) return;

    const fill = strengthBar.querySelector(".strength-fill");
    const percentage = (strength / 5) * 100;

    fill.style.width = percentage + "%";

    // Color based on strength
    if (strength <= 2) {
      fill.style.background = "linear-gradient(90deg, #F44336, #E53935)";
    } else if (strength <= 3) {
      fill.style.background = "linear-gradient(90deg, #FF9800, #FB8C00)";
    } else {
      fill.style.background = "linear-gradient(90deg, #4CAF50, #66BB6A)";
    }
  }

  // ==========================================
  // VALIDATION HELPER FUNCTIONS
  // ==========================================
  function showValidationMessage(input, message, type) {
    const formGroup = input.closest(".form-group");
    const messageDiv = document.createElement("div");
    messageDiv.className =
      type === "success" ? "success-message" : "error-message";

    const icon =
      type === "success"
        ? '<i class="fas fa-check-circle"></i>'
        : '<i class="fas fa-exclamation-circle"></i>';

    messageDiv.innerHTML = `${icon} <span>${message}</span>`;
    formGroup.appendChild(messageDiv);
  }

  function removeValidationMessage(input) {
    const formGroup = input.closest(".form-group");
    const existingMessage = formGroup.querySelector(
      ".error-message, .success-message"
    );
    if (existingMessage) {
      existingMessage.remove();
    }
  }

  // ==========================================
  // FORM SUBMIT VALIDATION
  // ==========================================
  if (form) {
    form.addEventListener("submit", function (e) {
      const fullName = fullNameInput.value.trim();
      const email = emailInput.value.trim();
      const phone = phoneInput.value.trim();
      const password = passwordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      let errors = [];

      // Validate all fields
      if (fullName === "" || fullName.length < 3) {
        errors.push("Nama lengkap minimal 3 karakter");
        fullNameInput.classList.add("invalid");
      }

      if (email === "" || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.push("Email tidak valid");
        emailInput.classList.add("invalid");
      }

      if (password === "" || password.length < 6) {
        errors.push("Password minimal 6 karakter");
        passwordInput.classList.add("invalid");
      }

      if (confirmPassword !== password) {
        errors.push("Password tidak cocok");
        confirmPasswordInput.classList.add("invalid");
      }

      if (errors.length > 0) {
        e.preventDefault();
        showToast(errors[0], "error");

        // Focus first invalid input
        const firstInvalid = form.querySelector(".invalid");
        if (firstInvalid) firstInvalid.focus();

        return false;
      }

      // Show loading state
      const submitBtn = form.querySelector(".btn-submit");
      submitBtn.classList.add("loading");
      submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> <span>Mendaftar...</span>';
      submitBtn.disabled = true;
    });
  }

  // ==========================================
  // INPUT ANIMATIONS
  // ==========================================
  const inputs = document.querySelectorAll(".form-group input");

  inputs.forEach((input) => {
    // Focus animation
    input.addEventListener("focus", function () {
      const label = this.closest(".form-group").querySelector("label");
      if (label) {
        label.style.color = "var(--secondary-color)";
        label.style.transform = "translateY(-2px)";
      }

      // Highlight form group
      this.closest(".form-group").style.background = "rgba(210, 105, 30, 0.03)";
    });

    // Blur animation
    input.addEventListener("blur", function () {
      const label = this.closest(".form-group").querySelector("label");
      if (label) {
        label.style.color = "var(--primary-color)";
        label.style.transform = "translateY(0)";
      }

      this.closest(".form-group").style.background = "transparent";
    });

    // Typing animation
    input.addEventListener("input", function () {
      this.style.transform = "scale(1.01)";
      setTimeout(() => {
        this.style.transform = "scale(1)";
      }, 100);
    });
  });

  // ==========================================
  // ALERT AUTO DISMISS
  // ==========================================
  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    // Add close button
    const closeBtn = document.createElement("button");
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.style.cssText = `
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s ease;
            font-size: 1.2rem;
            padding: 0.5rem;
        `;
    alert.style.position = "relative";
    alert.appendChild(closeBtn);

    closeBtn.addEventListener("mouseenter", function () {
      this.style.opacity = "1";
    });

    closeBtn.addEventListener("mouseleave", function () {
      this.style.opacity = "0.7";
    });

    closeBtn.addEventListener("click", function () {
      alert.style.animation = "slideOutRight 0.5s ease-out";
      setTimeout(() => {
        alert.remove();
      }, 500);
    });

    // Auto dismiss for success messages
    if (alert.classList.contains("alert-success")) {
      setTimeout(() => {
        if (alert.parentElement) {
          alert.style.animation = "slideOutRight 0.5s ease-out";
          setTimeout(() => {
            alert.remove();
          }, 500);
        }
      }, 5000);
    }
  });

  // ==========================================
  // TOAST NOTIFICATION
  // ==========================================
  function showToast(message, type = "info") {
    const toast = document.createElement("div");
    toast.className = "toast-notification";

    const colors = {
      success: "#4CAF50",
      error: "#F44336",
      warning: "#FF9800",
      info: "#2196F3",
    };

    const icons = {
      success: "fa-check-circle",
      error: "fa-exclamation-circle",
      warning: "fa-exclamation-triangle",
      info: "fa-info-circle",
    };

    toast.style.cssText = `
            position: fixed;
            bottom: -100px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            color: ${colors[type] || colors.info};
            padding: 1rem 2rem;
            border-radius: 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            z-index: 10000;
            font-weight: 600;
            transition: bottom 0.3s ease;
            border-left: 4px solid ${colors[type] || colors.info};
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 90%;
        `;

    toast.innerHTML = `
            <i class="fas ${icons[type] || icons.info}"></i>
            <span>${message}</span>
        `;

    document.body.appendChild(toast);

    setTimeout(() => {
      toast.style.bottom = "30px";
    }, 100);

    setTimeout(() => {
      toast.style.bottom = "-100px";
      setTimeout(() => toast.remove(), 300);
    }, 3000);

    toast.addEventListener("click", () => {
      toast.style.bottom = "-100px";
      setTimeout(() => toast.remove(), 300);
    });
  }

  window.showToast = showToast;

  // ==========================================
  // AUTO-FOCUS FIRST INPUT
  // ==========================================
  if (fullNameInput && fullNameInput.value === "") {
    setTimeout(() => {
      fullNameInput.focus();
    }, 500);
  }

  // ==========================================
  // FORM PROGRESS INDICATOR
  // ==========================================
  function updateFormProgress() {
    const totalFields = 5; // full_name, email, phone, password, confirm_password
    let filledFields = 0;

    if (fullNameInput && fullNameInput.value.trim() !== "") filledFields++;
    if (emailInput && emailInput.value.trim() !== "") filledFields++;
    if (phoneInput && phoneInput.value.trim() !== "") filledFields++;
    if (passwordInput && passwordInput.value !== "") filledFields++;
    if (confirmPasswordInput && confirmPasswordInput.value !== "")
      filledFields++;

    const progress = (filledFields / totalFields) * 100;

    const progressBar = document.querySelector(".form-progress-fill");
    if (progressBar) {
      progressBar.style.width = progress + "%";
    }
  }

  // Update progress on input
  inputs.forEach((input) => {
    input.addEventListener("input", updateFormProgress);
  });

  // ==========================================
  // PREVENT DOUBLE SUBMIT
  // ==========================================
  let isSubmitting = false;

  if (form) {
    form.addEventListener("submit", function (e) {
      if (isSubmitting) {
        e.preventDefault();
        return false;
      }
      isSubmitting = true;
    });
  }

});
