$(document).ready(function () {

    function loadStatistics(file) {
        $("#libraryStatistics").load(file);
    }

    loadStatistics("../snippets/books_statistics.php");

    $("#booksStatistics").on("click", function (e) {
        e.preventDefault();
        loadStatistics("../snippets/books_statistics.php");
    });

    $("#customersStatistics").on("click", function (e) {
        e.preventDefault();
        loadStatistics("../snippets/customers_statistics.php");
    });

    $("#employeesStatistics").on("click", function (e) {
        e.preventDefault();
        loadStatistics("../snippets/employee_statistics.php");
    });

});