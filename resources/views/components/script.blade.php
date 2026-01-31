<!-- jQuery & DataTables -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<!-- TomSelect -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<!-- Vite App.js (sudah include Alpine & Flowbite) -->
@vite('resources/js/app.js')

<!-- DataTables Init -->
<script>
    $(document).ready(function() {
        if ($('#myTable').length) {
            $('#myTable').DataTable();
        }
    });
</script>
