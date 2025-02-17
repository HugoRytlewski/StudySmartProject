document.addEventListener("DOMContentLoaded", function () {
    const progress = document.querySelector(".progress");
  
    const totalTime = 1; 
    let timeLeft = totalTime; 
  
    const interval = setInterval(function () {
      timeLeft--;
  
      const progressPercentage = ((totalTime - timeLeft) / totalTime) * 100;
      progress.style.width = `${progressPercentage}%`;
  
  
      if (timeLeft <= 0) {
        clearInterval(interval);
        window.location.href = "dashboard";
      }
    }, 1000); 
  });
  