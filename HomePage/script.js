// =======================================================
// CHỨC NĂNG CƠ BẢN
// =======================================================

// Smooth scrolling
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({ behavior: "smooth" });
    }
  });
});

// Scroll Animation Observer
const scrollObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry, index) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.classList.add("animate");
        }, index * 100);
      }
    });
  },
  { threshold: 0.1 }
);

document.querySelectorAll(".scroll-animate").forEach((el) => {
  scrollObserver.observe(el);
});

// =======================================================
// HIỆU ỨNG ĐẾM SỐ - PHẦN THÀNH TỰU
// =======================================================

function animateCounter(element, endValue, duration) {
  const startTime = performance.now();

  // Tách số và ký tự đặc biệt
  const hasPercent = endValue.includes("%");
  const hasPlus = endValue.includes("+");
  const hasDot = endValue.includes(".");

  // Lấy số thuần túy
  const targetNumber = parseFloat(endValue.replace(/[^0-9.]/g, ""));

  function update(currentTime) {
    const elapsed = currentTime - startTime;
    const progress = Math.min(elapsed / duration, 1);

    // Tính giá trị hiện tại
    let currentValue = progress * targetNumber;

    // Format số
    let displayText;
    if (hasDot) {
      displayText = currentValue.toFixed(1);
    } else {
      displayText = Math.floor(currentValue).toString();
    }

    // Thêm ký tự đặc biệt
    if (hasPlus) displayText += "+";
    if (hasPercent) displayText += "%";

    element.textContent = displayText;

    // Tiếp tục animation
    if (progress < 1) {
      requestAnimationFrame(update);
    } else {
      element.textContent = endValue; // Đảm bảo giá trị cuối chính xác
    }
  }

  requestAnimationFrame(update);
}

// Observer để kích hoạt animation khi scroll đến
function initCounterAnimation() {
  let hasAnimated = false;

  const counterObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting && !hasAnimated) {
          console.log("🎯 Counter animation triggered!");

          const numbers = entry.target.querySelectorAll(".number");

          // Dữ liệu IELTS
          const stats = [
            { value: "2005+", duration: 2500 },
            { value: "89%", duration: 2000 },
            { value: "6.5+", duration: 2500 },
            { value: "24+", duration: 2500 },
            { value: "10+", duration: 2000 },
          ];

          // Bắt đầu animation cho từng số
          numbers.forEach((num, index) => {
            if (stats[index]) {
              setTimeout(() => {
                animateCounter(num, stats[index].value, stats[index].duration);
              }, index * 100); // Delay mỗi số 100ms
            }
          });

          hasAnimated = true;
          counterObserver.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.3 }
  );

  // Bắt đầu observe
  const grid = document.querySelector(".achievements-grid");
  if (grid) {
    console.log("✅ Found achievements grid, observing...");
    counterObserver.observe(grid);
  } else {
    console.error("❌ Cannot find .achievements-grid element!");
  }
}

// =======================================================
// SLIDESHOW HERO - Cải thiện với pause on hover
// =======================================================

const heroSlideshow = document.querySelector(".hero-slideshow");
if (heroSlideshow) {
  heroSlideshow.addEventListener("mouseenter", function () {
    this.style.animationPlayState = "paused";
  });
  heroSlideshow.addEventListener("mouseleave", function () {
    this.style.animationPlayState = "running";
  });
}

// =======================================================
// KHỞI ĐỘNG KHI DOM LOADED
// =======================================================

document.addEventListener("DOMContentLoaded", function () {
  console.log("✅ DOM Loaded");
  initCounterAnimation();
});
// ... (Code cũ giữ nguyên) ...

// === CHATBOT LOGIC ===
const chatbotToggler = document.querySelector(".chatbot-toggler");
const closeBtn = document.querySelector(".close-btn");
const chatbox = document.querySelector(".chatbox");
const chatInput = document.querySelector(".chat-input textarea");
const sendChatBtn = document.querySelector(".chat-input span");

let userMessage = null; // Tin nhắn người dùng
const inputInitHeight = chatInput.scrollHeight;

// Tạo thẻ HTML cho tin nhắn
const createChatLi = (message, className) => {
  const chatLi = document.createElement("li");
  chatLi.classList.add("chat", `${className}`);
  let chatContent =
    className === "outgoing"
      ? `<p></p>`
      : `<span class="material-symbols-outlined"><i class="fas fa-robot"></i></span><p></p>`;
  chatLi.innerHTML = chatContent;
  chatLi.querySelector("p").textContent = message;
  return chatLi;
};

// Gửi tin nhắn và gọi API
const generateResponse = (chatElement) => {
  const messageElement = chatElement.querySelector("p");

  // Gửi yêu cầu đến file chatbot.php của chúng ta
  fetch("chatbot.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ message: userMessage }),
  })
    .then((res) => res.json())
    .then((data) => {
      messageElement.textContent = data.reply; // Hiển thị trả lời
    })
    .catch(() => {
      messageElement.classList.add("error");
      messageElement.textContent = "Xin lỗi, có lỗi kết nối. Vui lòng thử lại.";
    })
    .finally(() => chatbox.scrollTo(0, chatbox.scrollHeight));
};

const handleChat = () => {
  userMessage = chatInput.value.trim();
  if (!userMessage) return;

  chatInput.value = "";
  chatInput.style.height = `${inputInitHeight}px`;

  // 1. Hiển thị tin nhắn người dùng
  chatbox.appendChild(createChatLi(userMessage, "outgoing"));
  chatbox.scrollTo(0, chatbox.scrollHeight);

  // 2. Hiển thị trạng thái "Đang nhập..." của Bot
  setTimeout(() => {
    const incomingChatLi = createChatLi("Đang suy nghĩ...", "incoming");
    chatbox.appendChild(incomingChatLi);
    chatbox.scrollTo(0, chatbox.scrollHeight);
    generateResponse(incomingChatLi);
  }, 600);
};

// Xử lý sự kiện
chatInput.addEventListener("input", () => {
  chatInput.style.height = `${inputInitHeight}px`;
  chatInput.style.height = `${chatInput.scrollHeight}px`;
});

chatInput.addEventListener("keydown", (e) => {
  if (e.key === "Enter" && !e.shiftKey && window.innerWidth > 800) {
    e.preventDefault();
    handleChat();
  }
});

if (sendChatBtn) sendChatBtn.addEventListener("click", handleChat);
if (closeBtn)
  closeBtn.addEventListener("click", () =>
    document.body.classList.remove("show-chatbot")
  );
if (chatbotToggler)
  chatbotToggler.addEventListener("click", () =>
    document.body.classList.toggle("show-chatbot")
  );
