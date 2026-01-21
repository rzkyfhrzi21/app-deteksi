<!-- ================= JQUERY ================= -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo="
    crossorigin="anonymous"></script>

<!-- ================= CORE TEMPLATE (LOCAL - CUSTOM) ================= -->
<script src="assets/static/js/components/dark.js"></script>
<script src="assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="assets/static/js/pages/horizontal-layout.js"></script>
<script src="assets/compiled/js/app.js"></script>

<!-- ================= APEXCHARTS ================= -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.2"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="assets/static/js/pages/ui-apexchart.js"></script>
<script src="assets/static/js/pages/dashboard.js"></script>

<script>
    let totalLakiLaki = <?= $totalPendonorLakiLaki; ?>;
    let totalPerempuan = <?= $totalPendonorPerempuan; ?>;

    new ApexCharts(document.getElementById("chart-pendonor-jk"), {
        series: [totalLakiLaki, totalPerempuan],
        labels: ["Laki-laki", "Perempuan"],
        colors: ["#435ebe", "#55c6e8"],
        chart: {
            type: "donut",
            height: 350
        },
        legend: {
            position: "bottom"
        },
        plotOptions: {
            pie: {
                donut: {
                    size: "30%"
                }
            }
        }
    }).render();
</script>

<script>
    let totalBerhasil = <?= $totalPendonorBerhasil; ?>;
    let totalLayak = <?= $totalPendonorLayak; ?>;
    let totalGagal = <?= $totalPendonorGagal; ?>;

    new ApexCharts(document.getElementById("chart-riwayat-donor"), {
        series: [totalBerhasil, totalLayak, totalGagal],
        labels: ["Berhasil Donor", "Layak Tidak Donor", "Gagal Donor"],
        colors: ["#C82232", "#FEDA6F", "#000000"],
        chart: {
            type: "donut",
            height: 350
        },
        legend: {
            position: "bottom"
        },
        plotOptions: {
            pie: {
                donut: {
                    size: "30%"
                }
            }
        }
    }).render();
</script>

<script>
    let totalSelesai = <?= $totalKegiatanSelesai; ?>;
    let totalBerlangsung = <?= $totalKegiatanBerlangsung; ?>;
    let totalSegera = <?= $totalKegiatanSegera; ?>;

    new ApexCharts(document.getElementById("chart-riwayat-kegiatan"), {
        series: [totalSelesai, totalBerlangsung, totalSegera],
        labels: ["Kegiatan Selesai", "Sedang Berlangsung", "Segera"],
        colors: ["#F44336", "#FFC107", "#4CAF50"],
        chart: {
            type: "donut",
            height: 350
        },
        legend: {
            position: "bottom"
        },
        plotOptions: {
            pie: {
                donut: {
                    size: "30%"
                }
            }
        }
    }).render();
</script>

<!-- ================= SWEETALERT2 ================= -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php include 'sweetalert.php'; ?>

<!-- ================= CHOICES ================= -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="assets/static/js/pages/form-element-select.js"></script>

<!-- ================= PARSLEY ================= -->
<script src="https://cdn.jsdelivr.net/npm/parsleyjs@2/dist/parsley.min.js"></script>
<script src="assets/static/js/pages/parsley.js"></script>

<!-- ================= FLATPICKR ================= -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="assets/static/js/pages/date-picker.js"></script>

<!-- ================= FILEPOND ================= -->
<script src="https://cdn.jsdelivr.net/npm/filepond@4/dist/filepond.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/filepond-plugin-image-crop/dist/filepond-plugin-image-crop.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/filepond-plugin-image-filter/dist/filepond-plugin-image-filter.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script src="assets/static/js/pages/filepond.js"></script>

<!-- ================= CHECKBOX DELETE ================= -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const checkbox = document.getElementById("iaggree");
        const button = document.getElementById("btn-delete-account");
        if (checkbox && button) {
            checkbox.addEventListener("change", () =>
                checkbox.checked ? button.removeAttribute("disabled") : button.setAttribute("disabled", true)
            );
        }
    });
</script>

<!-- ================= DATATABLES ================= -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

<script>
    $(function() {
        $("#example1").DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

        $("#example2").DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            responsive: true
        });
    });
</script>