document.addEventListener("DOMContentLoaded", function () {
    const customerSearchInput = document.getElementById("customer_search");
    const customerSearchResults = document.getElementById("customer-search-results");
    const customerIdInput = document.getElementById("customer_id");

    customerSearchInput.addEventListener("input", function () {
        const query = customerSearchInput.value.trim();

        if (query.length < 2) {
            customerSearchResults.innerHTML = "";
            customerSearchResults.classList.remove("show");
            return;
        }

        fetch(`/customers/search?q=${encodeURIComponent(query)}`)
            .then((response) => response.json())
            .then((data) => {
                customerSearchResults.innerHTML = "";

                if (data.length > 0) {
                    data.forEach((customer) => {
                        const item = document.createElement("button");
                        item.className = "dropdown-item text-end";
                        item.textContent = customer.name;
                        item.dataset.id = customer.id;
                        item.addEventListener("click", function () {
                            customerSearchInput.value = customer.name;
                            customerIdInput.value = customer.id;
                            customerSearchResults.classList.remove("show");
                        });
                        customerSearchResults.appendChild(item);
                    });
                    customerSearchResults.classList.add("show");
                } else {
                    customerSearchResults.innerHTML = "<div class='dropdown-item disabled text-muted text-center'>موردی یافت نشد.</div>";
                    customerSearchResults.classList.add("show");
                }
            })
            .catch((error) => {
                console.error("Error fetching customers:", error);
            });
    });

    document.addEventListener("click", function (e) {
        if (!customerSearchResults.contains(e.target) && e.target !== customerSearchInput) {
            customerSearchResults.classList.remove("show");
        }
    });
});
