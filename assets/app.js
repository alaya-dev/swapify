import "./bootstrap.js";
import "./styles/app.css";

Pusher.logToConsole = true;

console.log("charhcour 🎉");
const pusher = new Pusher("7f4f9c9d2b396ad6ec87", { cluster: "eu" });
const Btn = document.querySelector("#send");
const input = document.querySelector("#content");
const conversationId = document.querySelector("#conversationId");
const msgs = document.querySelector("#messages");

const currentUserId = parseInt(
  document.querySelector("#currentUserId").value,
  10
);

document.addEventListener("DOMContentLoaded", () => {
  msgs.scrollTo(0, msgs.scrollHeight);
});

const sendMessage = async () => {
  const message = input.value.trim();
  if (!message) return;

  const payload = {
    content: message,
    conversationId: parseInt(conversationId.value, 10),
    userId: currentUserId,
  };

  input.value = "";

  try {
    await fetch("/messages", {
      method: "POST",
      
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(payload),
    });
  } catch (error) {
    console.error("Error sending message:", error);
  }
};

Btn.addEventListener("click", sendMessage);

const channel = pusher.subscribe("chat");

channel.bind("message-created", (data) => {
  const parent = document.createElement("div");
  const content = document.createElement("div");
  const parentClasses = [
    "flex",
    "mb-2",
    data.author.id === currentUserId ? "justify-end" : null,
  ].filter(Boolean);
  parent.classList.add(...parentClasses);
  const contentClasses = [
    "rounded",
    "py-2",
    "px-3",
    data.author.id === currentUserId ? "bg-green-100" : "bg-white",
  ].filter(Boolean);
  content.classList.add(...contentClasses);

  content.innerHTML = `
    <p class="text-sm text-teal-600 font-medium">${data.author.name}</p>
    <p class="text-sm mt-1 text-gray-800">${data.content}</p>
    <p class="text-right text-xs text-gray-500 mt-1">${data.createdAt}</p>
  `;

  parent.appendChild(content);
  msgs.appendChild(parent);
  msgs.scrollTo(0, msgs.scrollHeight);
});

 