$(document).ready(function() {
    $('#booksStatistics').click(function(e) {
        e.preventDefault();
        loadSnippet('../snippets/books_statistics.php');
    });

    $('#customersStatistics').click(function(e) {
        e.preventDefault();
        loadSnippet('../snippets/customers_statistics.php');
    });

    $('#librariansStatistics').click(function(e) {
        e.preventDefault();
        loadSnippet('../snippets/librarians_statistics.php');
    });

    function loadSnippet(url) {
        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#libraryStatistics').html(data);
            },
            error: function(xhr, status, error) {
                console.error('Error loading snippet:', error);
            }
        });
    }
});