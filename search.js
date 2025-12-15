const searchInput = document.getElementById("searchInput");
    const cards = document.querySelectorAll(".tech-card");

    searchInput.addEventListener("input", () => {
        const searchTerm = searchInput.value.toLowerCase();

        cards.forEach(card => {
            const title = card.getAttribute("data-title").toLowerCase();
            card.style.display = title.includes(searchTerm) ? "block" : "none";
        });
    });
