function toggleMenu() {
    const menu = document.getElementById('nav-menu');
    menu.classList.toggle('active');
}

// Zavření burger menu po kliknutí na odkaz
document.querySelectorAll('#nav-menu a').forEach(link => {
    link.addEventListener('click', () => {
        document.getElementById('nav-menu').classList.remove('active');
    });
});

  flatpickr("#date", {
    dateFormat: "Y-m-d", // formát data
    minDate: "today",    // minimalní dostupné datum je dnešek
    locale: "cs"         // nastavení na češtinu
  });

  flatpickr("#start_time", {
    enableTime: true,  // povolí výběr času
    noCalendar: true,  // skryje kalendář
    dateFormat: "H:i", // formát času (hodiny:minuty)
    time_24hr: true,   // 24 hodinový formát
  });

  flatpickr("#end_time", {
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i",
    time_24hr: true,
  });


  document.addEventListener("DOMContentLoaded", function () {
    const facilitySelect = document.getElementById("facility");
    const dateInput = document.getElementById("date");
    let bookedTimes = [];

    function fetchBookedTimes() {
        const facility = facilitySelect.value;
        const date = dateInput.value;

        if (!facility || !date) return;

        fetch(`get_booked_times.php?facility=${facility}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                bookedTimes = data.map(item => ({
                    from: item.start,
                    to: item.end
                }));

                setupTimePickers();
            });
    }

    function setupTimePickers() {
        flatpickr("#start_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 30,
            disable: bookedTimes
        });

        flatpickr("#end_time", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minuteIncrement: 30,
            disable: bookedTimes
        });
    }

    dateInput.addEventListener("change", fetchBookedTimes);
    facilitySelect.addEventListener("change", fetchBookedTimes);
});