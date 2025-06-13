document.addEventListener("DOMContentLoaded", function () {
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  const currentUrl = window.location.href;
  const navLinks = document.querySelectorAll(".navbar-nav .nav-link");

  navLinks.forEach((link) => {
    if (currentUrl.includes(link.getAttribute("href"))) {
      link.classList.add("active");
    }
  });

  // Pesan Flash-Hide Auto Setelah 5 Detik
  const flashMessages = document.querySelectorAll(
    ".alert:not(.alert-permanent)"
  );
  flashMessages.forEach((message) => {
    setTimeout(() => {
      message.classList.add("fade");
      setTimeout(() => {
        message.remove();
      }, 500);
    }, 5000);
  });

  // Konfirmasikan hapus
  const deleteButtons = document.querySelectorAll("[data-confirm]");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      if (!confirm(this.getAttribute("data-confirm"))) {
        e.preventDefault();
      }
    });
  });

  // Kategori input "lainnya"
  const categorySelect = document.getElementById("category");
  const newCategoryContainer = document.getElementById(
    "new-category-container"
  );

  if (categorySelect && newCategoryContainer) {
    categorySelect.addEventListener("change", function () {
      if (this.value === "other") {
        newCategoryContainer.classList.remove("d-none");
        document
          .getElementById("new_category")
          .setAttribute("required", "required");
      } else {
        newCategoryContainer.classList.add("d-none");
        document.getElementById("new_category").removeAttribute("required");
      }
    });
  }

  // Salinan yang tersedia tidak dapat melebihi total salinan
  const totalCopiesInput = document.getElementById("total_copies");
  const availableCopiesInput = document.getElementById("available_copies");

  if (totalCopiesInput && availableCopiesInput) {
    totalCopiesInput.addEventListener("change", function () {
      availableCopiesInput.setAttribute("max", this.value);
      if (parseInt(availableCopiesInput.value) > parseInt(this.value)) {
        availableCopiesInput.value = this.value;
      }
    });
  }
});
