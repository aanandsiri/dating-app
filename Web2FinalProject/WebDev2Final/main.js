// Contact form AJAX
document.addEventListener("DOMContentLoaded", () => {
  const cf = document.getElementById("contactForm");
  if (cf) {
    cf.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (!cf.checkValidity()) {
        cf.classList.add("was-validated");
        return;
      }
      const fd = new FormData(cf);
      try {
        const res = await fetch("contact.php", {
          method: "POST",
          headers: { "X-Requested-With": "fetch" },
          body: new URLSearchParams(fd),
        });
        const data = await res.json();
        const alertBox = document.getElementById("contactAlert");
        alertBox.className = data.ok
          ? "alert alert-success"
          : "alert alert-danger";
        alertBox.textContent = data.message || "Something went wrong";
        if (data.ok) cf.reset();
      } catch (err) {
        alert("Network error: " + err.message);
      }
    });
  }

  // Preferences save via cookies (client-side)
  const pf = document.getElementById("prefsForm");
  if (pf) {
    pf.addEventListener("submit", (e) => {
      e.preventDefault();
      const theme = pf.theme.value === "dark" ? "dark" : "light";
      const md = Math.max(1, parseInt(pf.max_distance.value || "50", 10));
      // 30-day cookies
      const expires = new Date(
        Date.now() + 30 * 24 * 60 * 60 * 1000
      ).toUTCString();
      document.cookie = `theme=${theme}; path=/; expires=${expires}`;
      document.cookie = `max_distance=${md}; path=/; expires=${expires}`;
      const alertBox = document.getElementById("prefsAlert");
      alertBox.className = "alert alert-success";
      alertBox.textContent = "Preferences saved. Reloading...";
      setTimeout(() => location.reload(), 700);
    });
  }

  // Like/Dislike (if present on page)
  const card = document.getElementById("profileCard");
  const likeBtn = document.querySelector(".btn-like");
  const dislikeBtn = document.querySelector(".btn-dislike");
  if (card && (likeBtn || dislikeBtn)) {
    async function react(action) {
      const profileId = card.dataset.id;
      const csrf = window.MM && MM.csrf ? MM.csrf : "";
      [likeBtn, dislikeBtn].forEach((b) => b && (b.disabled = true));
      try {
        const res = await fetch("reaction.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ action, profile_id: profileId, csrf }),
        });
        const data = await res
          .json()
          .catch(() => ({ ok: false, message: "Error" }));
        if (data.ok) {
          if (data.match) alert("It's a match! 🎉");
          location.reload();
        } else {
          alert(data.message || "Error saving reaction");
        }
      } catch (e) {
        alert("Network error: " + e.message);
      } finally {
        [likeBtn, dislikeBtn].forEach((b) => b && (b.disabled = false));
      }
    }
    likeBtn && likeBtn.addEventListener("click", () => react("like"));
    dislikeBtn && dislikeBtn.addEventListener("click", () => react("dislike"));
  }
});
