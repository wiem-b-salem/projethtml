// Email validation functions
function validateEmailOnInput(input) {
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailPattern.test(input.value)) {
        input.setCustomValidity('Veuillez entrer une adresse email valide');
    } else {
        input.setCustomValidity('');
    }
}

function validateEmailOnBlur(input) {
    validateEmailOnInput(input);
}

// Form validation and interactive functionality
document.addEventListener("DOMContentLoaded", function () {
    // Get form elements
    const registrationForm = document.getElementById("registration-form");
    const allergiesCheckbox = document.getElementById("allergies");
    const allergiesDetails = document.getElementById("allergies-details");
    const disabilityCheckbox = document.getElementById("disability");
    const disabilityDetails = document.getElementById("disability-details");
    const otherHealthCheckbox = document.getElementById("other-health-info");
    const otherDetails = document.getElementById("other-details");
  
    // Hide all textarea fields initially
    if (allergiesDetails) allergiesDetails.style.display = "none";
    if (disabilityDetails) disabilityDetails.style.display = "none";
    if (otherDetails) otherDetails.style.display = "none";
  
    // Toggle textarea visibility based on checkbox state
    if (allergiesCheckbox) {
        allergiesCheckbox.addEventListener("change", function () {
            if (allergiesDetails) {
                allergiesDetails.style.display = this.checked ? "block" : "none";
                if (this.checked) {
                    allergiesDetails.setAttribute("required", "required");
                } else {
                    allergiesDetails.removeAttribute("required");
                    allergiesDetails.value = "";
                }
            }
        });
    }
  
    if (disabilityCheckbox) {
        disabilityCheckbox.addEventListener("change", function () {
            if (disabilityDetails) {
                disabilityDetails.style.display = this.checked ? "block" : "none";
                if (this.checked) {
                    disabilityDetails.setAttribute("required", "required");
                } else {
                    disabilityDetails.removeAttribute("required");
                    disabilityDetails.value = "";
                }
            }
        });
    }
  
    if (otherHealthCheckbox) {
        otherHealthCheckbox.addEventListener("change", function () {
            if (otherDetails) {
                otherDetails.style.display = this.checked ? "block" : "none";
                if (this.checked) {
                    otherDetails.setAttribute("required", "required");
                } else {
                    otherDetails.removeAttribute("required");
                    otherDetails.value = "";
                }
            }
        });
    }
  
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
  
    // Form submission handling
    if (registrationForm) {
        registrationForm.addEventListener("submit", function (e) {
            e.preventDefault();
            let errors = [];
  
            // Validate parent first name
            const firstNameInput = document.getElementById("first-name");
            if (!firstNameInput || !firstNameInput.value || !validateName(firstNameInput.value)) {
                errors.push(
                    "Le prénom du parent n'est pas valide. Utilisez uniquement des lettres, espaces, tirets ou apostrophes."
                );
            }

            // Validate parent last name
            const lastNameInput = document.getElementById("last-name");
            if (!lastNameInput || !lastNameInput.value || !validateName(lastNameInput.value)) {
                errors.push(
                    "Le nom de famille du parent n'est pas valide. Utilisez uniquement des lettres, espaces, tirets ou apostrophes."
                );
            }
  
            // Validate child name
            const childNameInput = document.getElementById("child-name");
            if (!childNameInput || !childNameInput.value || !validateName(childNameInput.value)) {
                errors.push(
                    "Le nom de l'enfant n'est pas valide. Utilisez uniquement des lettres, espaces, tirets ou apostrophes."
                );
            }
  
            // Validate email
            const emailInput = document.getElementById("email");
            if (!emailInput || !emailInput.value) {
                errors.push("L'adresse email est requise.");
            }
  
            // Validate primary phone
            const phoneInput = document.getElementById("phone");
            if (!phoneInput || !phoneInput.value || !validatePhone(phoneInput.value)) {
                errors.push(
                    "Le numéro de téléphone doit contenir exactement 8 chiffres."
                );
            }
  
            // Validate secondary phone if provided
            const secondaryPhoneInput = document.getElementById("phone-secondary");
            if (secondaryPhoneInput && secondaryPhoneInput.value && !validatePhone(secondaryPhoneInput.value)) {
                errors.push(
                    "Le numéro de téléphone secondaire doit contenir exactement 8 chiffres."
                );
            }
  
            // Validate age
            const ageInput = document.getElementById("age");
            if (!ageInput || !ageInput.value) {
                errors.push("L'âge de l'enfant est requis.");
            }
  
            // Validate level
            const levelInput = document.getElementById("level");
            if (!levelInput || !levelInput.value) {
                errors.push("Le niveau d'éducation est requis.");
            }
  
            // Validate terms acceptance
            const termsCheckbox = document.getElementById("terms");
            if (!termsCheckbox || !termsCheckbox.checked) {
                errors.push("Vous devez accepter les termes et conditions pour continuer.");
            }
  
            // Display errors if any
            if (errors.length > 0) {
                alert("Erreurs de validation:\n\n" + errors.join("\n"));
                return;
            }
  
            // If no errors, submit the form
            this.submit();
        });
    }
  
    // Age-dependent level selection
    const ageSelect = document.getElementById("age");
    const levelSelect = document.getElementById("level");
  
    if (ageSelect && levelSelect) {
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
    }
});
  