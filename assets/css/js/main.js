const counters = document.querySelectorAll(".counter");

counters.forEach((counter) => {
  const updateCounter = () => {
    const target = +counter.innerText;

    let count = 0;

    const speed = target / 50;

    const run = () => {
      count += speed;

      if (count < target) {
        counter.innerText = Math.ceil(count);

        requestAnimationFrame(run);
      } else {
        counter.innerText = target;
      }
    };

    run();
  };

  updateCounter();
});
