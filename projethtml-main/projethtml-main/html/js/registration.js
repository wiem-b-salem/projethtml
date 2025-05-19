// Form validation and interactive functionality
document.addEventListener("DOMContentLoaded", function () {
    // Get form elements
    const registrationForm = document.getElementById("registration-form");
    const healthForm = document.getElementById("health-form");
    const allergiesCheckbox = document.getElementById("allergies");
    const allergiesDetails = document.getElementById("allergies-details");
    const disabilityCheckbox = document.getElementById("disability");
    const disabilityDetails = document.getElementById("disability-details");
    const otherHealthCheckbox = document.getElementById("other-health-info");
    const otherDetails = document.getElementById("other-details");
  
    // Hide all textarea fields initially
    allergiesDetails.style.display = "none";
    disabilityDetails.style.display = "none";
    otherDetails.style.display = "none";
  
    // Toggle textarea visibility based on checkbox state
    allergiesCheckbox.addEventListener("change", function () {
      allergiesDetails.style.display = this.checked ? "block" : "none";
      if (this.checked) {
        allergiesDetails.setAttribute("required", "required");
      } else {
        allergiesDetails.removeAttribute("required");
        allergiesDetails.value = "";
      }
    });
  
    disabilityCheckbox.addEventListener("change", function () {
      disabilityDetails.style.display = this.checked ? "block" : "none";
      if (this.checked) {
        disabilityDetails.setAttribute("required", "required");
      } else {
        disabilityDetails.removeAttribute("required");
        disabilityDetails.value = "";
      }
    });
  
    otherHealthCheckbox.addEventListener("change", function () {
      otherDetails.style.display = this.checked ? "block" : "none";
      if (this.checked) {
        otherDetails.setAttribute("required", "required");
      } else {
        otherDetails.removeAttribute("required");
        otherDetails.value = "";
      }
    });
  
    // Content validation functions
    function validateName(name) {
      // Check if name contains only letters, spaces, hyphens, and apostrophes
      const nameRegex = /^[a-zA-ZÀ-ÿ\s'-]{2,50}$/;
      return nameRegex.test(name) && name.trim().length >= 2;
    }
  
    function validatePhone(phone) {
      // Remove any non-digit characters from the phone number
      const cleanPhone = phone.replace(/\D/g, "");
      // Check if it contains exactly 8 digits
      return /^\d{8}$/.test(cleanPhone);
    }
  
    function validateEmail(email) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      return emailRegex.test(email) && email.length <= 100;
    }
  
    function validateTextContent(text, minLength = 10, maxLength = 500) {
      // Check if text contains meaningful content and not just random characters
      const meaningfulTextRegex = /^[a-zA-ZÀ-ÿ0-9\s.,!?'-]+$/;
      return (
        text.length >= minLength &&
        text.length <= maxLength &&
        meaningfulTextRegex.test(text)
      );
    }
  
    // Add input event listeners for phone fields to enforce 8 digits
    const phoneInputs = [
      document.getElementById("phone"),
      document.getElementById("phone-secondary"),
    ];
  
    phoneInputs.forEach((input) => {
      if (input) {
        input.addEventListener("input", function () {
          // Remove non-digits
          let cleaned = this.value.replace(/\D/g, "");
          // Limit to 8 digits
          cleaned = cleaned.substring(0, 8);
          this.value = cleaned;
        });
      }
    });
  
    // Generate unique registration ID
    function generateRegistrationId() {
      return "REG_" + Date.now() + "_" + Math.random().toString(36).substr(2, 9);
    }
  
    // Save registration data to localStorage
    function saveRegistration(formData) {
      const registrationId = generateRegistrationId();
      const registrations = JSON.parse(
        localStorage.getItem("registrations") || "[]"
      );
  
      formData.registrationId = registrationId;
      formData.registrationDate = new Date().toISOString();
      formData.status = "En attente";
  
      registrations.push(formData);
      localStorage.setItem("registrations", JSON.stringify(registrations));
  
      return registrationId;
    }
  
    // Form submission handling
    registrationForm.addEventListener("submit", function (e) {
      e.preventDefault();
      let errors = [];
  
      // Validate parent name
      const parentName = document.getElementById("parent-name").value;
      if (!validateName(parentName)) {
        errors.push(
          "Le nom du parent n'est pas valide. Utilisez uniquement des lettres, espaces, tirets ou apostrophes."
        );
      }
  
      // Validate child name
      const childName = document.getElementById("child-name").value;
      if (!validateName(childName)) {
        errors.push(
          "Le nom de l'enfant n'est pas valide. Utilisez uniquement des lettres, espaces, tirets ou apostrophes."
        );
      }
  
      // Validate email
      const email = document.getElementById("email").value;
      if (!validateEmail(email)) {
        errors.push("L'adresse email n'est pas valide.");
      }
  
      // Validate primary phone
      const phone = document.getElementById("phone").value;
      if (!validatePhone(phone)) {
        errors.push(
          "Le numéro de téléphone doit contenir exactement 8 chiffres."
        );
      }
  
      // Validate secondary phone if provided
      const secondaryPhone = document.getElementById("phone-secondary").value;
      if (secondaryPhone && !validatePhone(secondaryPhone)) {
        errors.push(
          "Le numéro de téléphone secondaire doit contenir exactement 8 chiffres."
        );
      }
  
      // Display errors if any
      if (errors.length > 0) {
        alert("Erreurs de validation:\n\n" + errors.join("\n"));
        return;
      }
  
      // Collect form data
      const formData = {
        parentName,
        email,
        phone,
        secondaryPhone,
        childName,
        age: document.getElementById("age").value,
        level: document.getElementById("level").value,
      };
  
      // Save first part of registration
      localStorage.setItem("tempRegistration", JSON.stringify(formData));
  
      // Enable health form
      document.getElementById("health-form").style.opacity = "1";
      document.getElementById("health-form").style.pointerEvents = "auto";
  
      // Scroll to health form
      document
        .getElementById("health-form")
        .scrollIntoView({ behavior: "smooth" });
    });
  
    // Health form validation and submission
    healthForm.addEventListener("submit", function (e) {
      e.preventDefault();
      let errors = [];
  
      // Validate allergies details if checkbox is checked
      if (allergiesCheckbox.checked) {
        const allergiesText = allergiesDetails.value;
        if (!validateTextContent(allergiesText, 10, 500)) {
          errors.push(
            "Veuillez fournir une description détaillée et valide des allergies (10-500 caractères)."
          );
        }
      }
  
      // Validate disability details if checkbox is checked
      if (disabilityCheckbox.checked) {
        const disabilityText = disabilityDetails.value;
        if (!validateTextContent(disabilityText, 10, 500)) {
          errors.push(
            "Veuillez fournir une description détaillée et valide des besoins particuliers (10-500 caractères)."
          );
        }
      }
  
      // Validate other health details if checkbox is checked
      if (otherHealthCheckbox.checked) {
        const otherText = otherDetails.value;
        if (!validateTextContent(otherText, 10, 500)) {
          errors.push(
            "Veuillez fournir une description détaillée et valide des autres informations de santé (10-500 caractères)."
          );
        }
      }
  
      // Check terms acceptance
      const termsCheckbox = document.getElementById("terms");
      if (!termsCheckbox.checked) {
        errors.push(
          "Vous devez accepter les termes et conditions pour continuer."
        );
      }
  
      // Display errors if any
      if (errors.length > 0) {
        alert("Erreurs de validation:\n\n" + errors.join("\n"));
        return;
      }
  
      // Get the temporary registration data
      const registrationData = JSON.parse(
        localStorage.getItem("tempRegistration") || "{}"
      );
  
      // Add health information
      registrationData.healthInfo = {
        hasAllergies: allergiesCheckbox.checked,
        allergiesDetails: allergiesCheckbox.checked ? allergiesDetails.value : "",
        hasDisability: disabilityCheckbox.checked,
        disabilityDetails: disabilityCheckbox.checked
          ? disabilityDetails.value
          : "",
        hasOtherHealthInfo: otherHealthCheckbox.checked,
        otherHealthDetails: otherHealthCheckbox.checked ? otherDetails.value : "",
      };
  
      // Save complete registration
      const registrationId = saveRegistration(registrationData);
  
      // Clear temporary storage
      localStorage.removeItem("tempRegistration");
  
      // Show success message with registration ID
      alert(
        `Inscription réussie !\n\nVotre numéro d'inscription est : ${registrationId}\n\nNous vous contacterons bientôt pour planifier une réunion.`
      );
  
      // Redirect to homepage after successful registration
      setTimeout(() => {
        window.location.href = "homepage.html";
      }, 2000);
    });
  
    // Age-dependent level selection
    const ageSelect = document.getElementById("age");
    const levelSelect = document.getElementById("level");
  
    ageSelect.addEventListener("change", function () {
      const age = parseInt(this.value);
      levelSelect.value = "";
  
      // Update education level options based on age
      Array.from(levelSelect.options).forEach((option) => {
        if (option.value) {
          const grade = parseInt(option.value.match(/\d+/)[0]);
          option.disabled = Math.abs(grade - age) > 1;
        }
      });
    });
  
    // Initially disable health form until registration form is completed
    document.getElementById("health-form").style.opacity = "0.5";
    document.getElementById("health-form").style.pointerEvents = "none";
  });
  