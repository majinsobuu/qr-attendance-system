// Advanced toast message
function showToast(message, type = 'info') {
    const toast = document.createElement("div");
    toast.innerText = message;
    
    // Base styles
    toast.style.position = "fixed";
    toast.style.bottom = "20px";
    toast.style.right = "20px";
    toast.style.background = "rgba(30, 41, 59, 0.9)";
    toast.style.backdropFilter = "blur(12px)";
    toast.style.color = "#f8fafc";
    toast.style.padding = "15px 24px";
    toast.style.borderRadius = "12px";
    toast.style.boxShadow = "0 10px 25px -5px rgba(0, 0, 0, 0.4)";
    toast.style.borderLeft = "4px solid #38bdf8"; // default blue accent
    toast.style.zIndex = "9999";
    toast.style.fontFamily = "'Inter', sans-serif";
    toast.style.fontWeight = "500";
    toast.style.transform = "translateX(120%)";
    toast.style.transition = "transform 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55), opacity 0.3s";
    toast.style.opacity = "0";

    // Set accent based on type
    if (type === 'success') toast.style.borderLeftColor = "#10b981";
    if (type === 'danger') toast.style.borderLeftColor = "#ef4444";
    if (type === 'warning') toast.style.borderLeftColor = "#f59e0b";

    document.body.appendChild(toast);

    // Prompt animation in
    setTimeout(() => {
        toast.style.transform = "translateX(0)";
        toast.style.opacity = "1";
    }, 10);

    // Animation out
    setTimeout(() => {
        toast.style.transform = "translateX(120%)";
        toast.style.opacity = "0";
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}