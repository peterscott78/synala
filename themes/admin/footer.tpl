
            </div>
          </div>
        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->

      <!-- Main Footer -->
      <footer class="main-footer">
        <!-- To the right -->
        <div class="pull-right hidden-xs">
          Turning visions into reality...
        </div>
        <!-- Default to the left --> 
        <strong>Developed By <a href="http://envrin.com/">Envrin Group</a>.  2015</strong> 
      </footer>

    </div><!-- ./wrapper -->

    <!-- REQUIRED JS SCRIPTS -->
    
    <!-- jQuery 2.1.3 -->
    <script src="{$theme_uri}/plugins/jQuery/jQuery-2.1.3.min.js"></script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="{$theme_uri}/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <!-- AdminLTE App -->
    <script src="{$theme_uri}/dist/js/app.min.js" type="text/javascript"></script>
    <script src="{$theme_uri}/plugins/iCheck/icheck.min.js" type="text/javascript"></script>
    <script src="{$theme_uri}/plugins/chartjs/Chart.min.js" type="text/javascript"></script>
    <!-- Optionally, you can add Slimscroll and FastClick plugins. 
          Both of these plugins are recommended to enhance the 
          user experience -->

    <script type="text/javascript">
        $('input[type="checkbox"], input[type="radio"]').iCheck({
          checkboxClass: 'icheckbox_square-aero',
          radioClass: 'iradio_square-aero'
        });

        $('.signing_method').on('ifChecked', function() { changeSigningMethod(); } );
        $('.autogen_keys').on('ifChecked', function() { changeAutoGenKeys(); } );
    </script>


{if {$route} eq 'admin/index'}
    
    <script type="text/javascript">

      $(function () {

          // Initialize chart
          var revenueChartCanvas = $("#revenueChart").get(0).getContext("2d");
          var revenueChart = new Chart(revenueChartCanvas);

        // Set chart data
        var revenueChartData = {
          labels: [{$revenue_chart_labels}], 
          datasets: [
          {
            label: "Revenue", 
            fillColor: "rgba(60,141,188,0.9)",
            strokeColor: "rgba(60,141,188,0.8)",
            pointColor: "#3b8bba",
            pointStrokeColor: "rgba(60,141,188,1)",
            pointHighlightFill: "#fff",
            pointHighlightStroke: "rgba(60,141,188,1)",
            data: [{$revenue_chart_data}]
          }
        ]
      };

      var revenueChartOptions = {
        showScale: true, 
        scaleShowGridLines: false,
        scaleGridLineColor: "rgba(0,0,0,.05)", 
        scaleGridLineWidth: 1, 
        scaleShowHorizontalLines: true, 
        scaleShowVerticalLines: true, 
        bezierCurve: true, 
        bezierCurveTension: 0.3, 
        pointDot: false, 
        pointDotRadius: 4, 
        pointDotStrokeWidth: 1, 
        pointHitDetectionRadius: 20, 
        datasetStroke: true, 
        datasetStrokeWidth: 2, 
        datasetFill: true, 
        maintainAspectRatio: false, 
        responsive: true
      };

      // Create chart
      revenueChart.Line(revenueChartData, revenueChartOptions);

    });
  </script>
{/if}

  </body>
</html>