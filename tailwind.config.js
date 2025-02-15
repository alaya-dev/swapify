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
      },
      animation: {
        pulse: "pulse 1.5s ease-in-out infinite",
        glow: "glow 1.5s ease-in-out infinite",
      },
    },
  },
  plugins: [],
};
