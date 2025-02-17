/** @type {import('tailwindcss').Config} */
const { keyframes, animation } = require("tailwindcss/defaultTheme");

module.exports = {
  content: ["./assets/**/*.js", "./templates/**/*.html.twig"],
  theme: {
    extend: {
      keyframes: {
        pulse: {
          "0%, 100%": { opacity: "1" },
          "50%": { opacity: "0.5" },
        },
        glow: {
          "0%, 100%": { boxShadow: "0 0 5px #ef4444, 0 0 10px #ef4444" },
          "50%": { boxShadow: "0 0 10px #ef4444, 0 0 20px #ef4444" },
        },
        spin: {
          "0%": { transform: "rotate(0deg)" },
          "50%": { transform: "rotate(180deg)" },
          "100%": { transform: "rotate(180deg)" },
        },
      },
      animation: {
        pulse: "pulse 1.5s ease-in-out infinite",
        glow: "glow 1.5s ease-in-out infinite",
        "spin-slow": "spin 3s linear infinite", // Custom slow spin animation
      },
    },
  },
  plugins: [],
};
