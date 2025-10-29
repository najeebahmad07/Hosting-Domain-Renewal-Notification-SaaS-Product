<?php
// settings.php
?>

<div class="settings-container">
  <h3>Dashboard Settings</h3>
  <hr>

  <div class="mode-section">
    <h5>Theme Mode</h5>
    <button id="lightMode" class="btn btn-outline-secondary">Light Mode</button>
    <button id="darkMode" class="btn btn-outline-secondary">Dark Mode</button>
  </div>

  <div class="color-section mt-3">
    <h5>Theme Color</h5>
    <div class="color-palette">
      <button class="color-btn" data-color="#0d6efd" style="background:#0d6efd"></button>
      <button class="color-btn" data-color="#198754" style="background:#198754"></button>
      <button class="color-btn" data-color="#dc3545" style="background:#dc3545"></button>
      <button class="color-btn" data-color="#ffc107" style="background:#ffc107"></button>
      <button class="color-btn" data-color="#6f42c1" style="background:#6f42c1"></button>
    </div>
  </div>
</div>

<style>
.settings-container {
  padding: 20px;
}
.color-palette {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}
.color-btn {
  width: 35px;
  height: 35px;
  border: none;
  border-radius: 50%;
  cursor: pointer;
}
</style>

<script>
const root = document.documentElement;

// Light & Dark Mode
document.getElementById("lightMode").addEventListener("click", () => {
  root.style.setProperty('--background-color', '#f8f9fa');
  root.style.setProperty('--text-color', '#000');
  localStorage.setItem('themeMode', 'light');
});

document.getElementById("darkMode").addEventListener("click", () => {
  root.style.setProperty('--background-color', '#0d1117');
  root.style.setProperty('--text-color', '#fff');
  localStorage.setItem('themeMode', 'dark');
});

// Color theme change
document.querySelectorAll(".color-btn").forEach(btn => {
  btn.addEventListener("click", () => {
    const color = btn.dataset.color;
    root.style.setProperty('--theme-color', color);
    localStorage.setItem('themeColor', color);
  });
});

// Restore saved settings on load
window.addEventListener('load', () => {
  const savedColor = localStorage.getItem('themeColor');
  const savedMode = localStorage.getItem('themeMode');
  if (savedColor) root.style.setProperty('--theme-color', savedColor);
  if (savedMode === 'dark') {
    root.style.setProperty('--background-color', '#0d1117');
    root.style.setProperty('--text-color', '#fff');
  }
});
</script>
