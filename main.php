<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">

  <title>Environmental Monitoring System</title>
  <link rel="stylesheet" href="jsModules/helper.css?v=17" />
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Arial, sans-serif;
    }

    .container {
      display: flex;
      height: 100vh;
      width: 100vw;
    }

    .sidebar {
      min-width: 250px;
      max-width: 400px;
      max-height: 100vh;
      background: #2d5c88;
      color: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding-top: 24px;

    }

    .sidebar-header {
      margin-bottom: 80px;

      text-align: center;
    }

    .sidebar-logo {
      width: 200px;
      height: 130px;
      border-radius: 25%;
      background: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 16px;
    }

    .sidebar-logo img {
      max-width: 200px;
      max-height: 130px;
    }

    .sidebar-menu {
      width: 100%;
    }

    .sidebar-link {
      display: flex;
      align-items: center;
      padding: 12px 32px;
      color: #b0c4de;
      text-decoration: none;
      font-size: 16px;
      transition: background 0.2s, color 0.2s;
      border-radius: 24px 0 0 24px;
      margin-bottom: 4px;
    }

    .sidebar-link .icon {
      margin-right: 12px;
      font-size: 20px;
    }

    .sidebar-link.active,
    .sidebar-link:hover {
      background: #fff;
      color: #2d5c88;
    }

    .sidebar-link.disabled {
      color: #7a97b7;
      pointer-events: none;
      opacity: 0.6;
    }

    .sidebar-footer {
      margin-top: auto;
      width: 100%;
      padding-bottom: 24px;
    }

    .main-content {
      width: 88vw;
      min-width: 300px;
      background: #f5f6f7;
      min-height: 100vh;
      font-family: 'Segoe UI', Arial, sans-serif;
    }

    .overview-title {
      font-size: 32px;
      font-weight: bold;
      color: #444;
      margin-left: 60px;
      margin-bottom: 24px;
      letter-spacing: 1px;
    }

    .search-bar {
      margin-left: 60px;
      margin-bottom: 32px;
      position: relative;
      width: 340px;
    }

    .search-bar input {
      width: 100%;
      padding: 12px 40px 12px 16px;
      border-radius: 24px;
      border: none;
      background: #fff;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
      font-size: 16px;
      outline: none;
    }

    .search-bar .search-icon {
      position: absolute;
      right: 16px;
      top: 12px;
      font-size: 20px;
      color: #b0c4de;
    }

    .district-select {
      position: absolute;
      right: 60px;
      top: 40px;
    }

    .district-select button {
      background: #3c5e8b;
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 8px 24px;
      font-size: 16px;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .overview-panel {
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.09);
      padding: 24px;
      margin-left: 60px;
      width: 400px;
      margin-bottom: 32px;
    }

    .overview-panel .district-group {
      margin-bottom: 16px;
    }

    .overview-panel .district-title {
      background: #bada1a;
      color: #fff;
      font-weight: bold;
      padding: 8px 16px;
      border-radius: 8px;
      margin-bottom: 8px;
      font-size: 18px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
    }

    .overview-panel .location-list {
      display: flex;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 8px;
      margin-left: 8px;
    }

    .overview-panel .location-list label {
      display: flex;
      align-items: center;
      font-size: 15px;
      margin-right: 16px;
      color: #444;
    }

    .overview-panel input[type="checkbox"] {
      margin-right: 6px;
    }

    .overview-map {
      position: absolute;
      left: 500px;
      top: 120px;
      width: 700px;
      height: 500px;
      background: transparent;
    }

    .overview-map img {
      width: 100%;
      border-radius: 24px;
      box-shadow: 0 2px 16px rgba(0, 0, 0, 0.07);
    }

    .map-marker {
      position: absolute;
      width: 40px;
      height: 40px;
      background: none;
      /* You can use an <img> or SVG for marker */
    }

    .info-card {
      position: absolute;
      left: 500px;
      top: 650px;
      width: 400px;
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.09);
      padding: 24px;
      display: flex;
      gap: 16px;
      align-items: center;
    }

    .info-card img {
      width: 120px;
      height: 80px;
      object-fit: cover;
      border-radius: 16px;
    }

    .info-card-details {
      flex: 1;
    }

    .info-card-details .location-title {
      font-weight: bold;
      font-size: 18px;
      margin-bottom: 8px;
      color: #444;
    }

    .info-card-details .location-desc {
      font-size: 15px;
      color: #666;
      margin-bottom: 4px;
    }

    #dataAnalysisPageLink,
    #EventLogPageLink,
    #dataAnalysisPageLink1 {
      display: none;
    }
  </style>

  <script type="text/javascript" src="/~vidplat/current/jsModules/jquery.min.js"></script>
  <script type="text/javascript" src="/~vidplat/current/jsModules/moment.js"></script>
  <script type="text/javascript" src="jsModules/constants.js"></script>
  <script type="text/javascript" src="jsModules/helper.js"></script>

  <script type="text/javascript">
    var userId = <?php echo (($_REQUEST['userId'] == "") ? -1 : $_REQUEST['userId']); ?>;
    var userType = <?php echo (($_REQUEST['userType'] == "") ? -1 : $_REQUEST['userType']); ?>;
    var userName = "<?php echo ($_REQUEST['userName']); ?>";
    var token = "<?php echo ($_REQUEST['token']); ?>";
    var dispCtx = null

    const USERS_API_PREFIX = "/~vidplat/webapi/a.php?m=Users"
    const SITES_API_PREFIX = "/~vidplat/webapi/a.php?m=Sites"

    function setActiveSidebar(pageId) {
      $('.sidebar-link').removeClass('active');
      $(`#${pageId}`).addClass('active');
    }

    //function homePageLinkClicked() {
    //  setActiveSidebar('homePageLink');
    //  var userId = $('#userId').val();
    //  var userType = $('#userType').val();
    //  var userName = $('#userName').val();
    //  var token = $('#token').val();
    //  var params = `userId=${encodeURIComponent(userId)}&userType=${encodeURIComponent(userType)}&userName=${encodeURIComponent(userName)}&token=${encodeURIComponent(token)}`;
    //  document.getElementById('mainFrame').src = 'home.php?' + params;
    //}

    function liveViewPageLinkClicked() {
      setActiveSidebar('liveViewPageLink');
      $('#generalForm').attr('action', 'liveview.php');
      $('#generalForm').attr('target', 'mainFrame');
      $("#generalForm").submit();

    }

    function PlaybackPageLinkClicked() {
      setActiveSidebar('PlaybackPageLink');
      $('#generalForm').attr('action', 'playback.php');
      $('#generalForm').attr('target', 'mainFrame');
      $("#generalForm").submit();
    }

    function TimeLapsePageLinkClicked() {
      setActiveSidebar('TimeLapsePageLink');
      $('#generalForm').attr('action', 'timelapse.php');
      $('#generalForm').attr('target', 'mainFrame');
      $("#generalForm").submit();
    }

    function DownloadPageLinkClicked() {
      setActiveSidebar('DownloadPageLink');
      $('#generalForm').attr('action', 'download.php');
      $('#generalForm').attr('target', 'mainFrame');
      $("#generalForm").submit();
    }

    function EventLogPageLinkClicked() {
      setActiveSidebar('EventLogPageLink');
      $('#generalForm').attr('action', 'eventlog.php');
      $('#generalForm').attr('target', 'mainFrame');
      $("#generalForm").submit();
    }

    function dataAnalysisPageLinkClicked() {
      setActiveSidebar('dataAnalysisPageLink');
      $('#generalForm').attr('action', 'dataanalysis.php');
      $('#generalForm').attr('target', 'mainFrame');
      $("#generalForm").submit();
    }

    function dataAnalysisPageLink1Clicked() {
      setActiveSidebar('dataAnalysisPageLink1');
      $('#generalForm').attr('action', 'dataanalysis1.php');
      $('#generalForm').attr('target', 'mainFrame');
      $("#generalForm").submit();
    }


    function logoutLinkClicked() {
      setActiveSidebar('logoutLink');
      $.post(`${USERS_API_PREFIX}&a=logout`, `token=${token}`,
        function (data) {
          if (parseInt(data.code, 10) === 0) {
            console.log(data)
          } else {
            console.log(data)
          }

          action = "index.php"
          $('#generalForm').attr('action', action);
          $('#generalForm').attr('target', '_blank');
          $("#generalForm").submit()
        }
      )
    }

    function fetchDispInfo() {
      var dC = {}
      $.ajax({
        type: 'POST',
        url: `${SITES_API_PREFIX}&a=getSiteGroup`,
        data: `userId=${userId}&token=${token}`,
        success: function (data) {
          if (parseInt(data.code, 10) === 0) {
            if (data.data != null) {
              dC['SGKy'] = objFlip(data.data.sgInfo.colNames);
              dC['SGData'] = data.data.sgInfo.rowData;
            } else {
              dC['SGKy'] = null
              dC['SGData'] = null
            }

            dC['SGIdxCur'] = null
            dC['SiteIdxCur'] = null

            if (dC['SGData'] && dC['SGData'].length > 0) {
              dC['SGIdxCur'] = 0 
              dC['SiteIdxCur'] = 0 
              dC['SitesKy'] = objFlip(data.data.sgSiteInfo.colNames)
              dC['SGLU'] = data.data.sgSiteInfo.rowData // Site group lookup
            }
          } else {
            console.log(data);
            if (data.indexOf("Not logged yet.") > 0)
              window.open('index.php', '_top')
          }
        },
        error: function (xhr, status, error) {
          console.log(error);
        },
        async: false,
      });

      return dC
    }

    $(document).ready(async function () {

      dispCtx = fetchDispInfo()

      // get the site groupdata type:
      console.log(dispCtx.SGData[0][dispCtx.SGKy.type])

      // Hide dataAnalysisPageLink and EventLogPageLink if type is people_cnt
      //if (dispCtx.SGData[0][dispCtx.SGKy.type] === 'people_cnt') {
      //  $('#dataAnalysisPageLink1').css('display', 'flex').removeClass('active');
      //} else if (dispCtx.SGData[0][dispCtx.SGKy.type] === 'event') {
      //  $('#dataAnalysisPageLink').css('display', 'flex');
      //}

      $('#EventLogPageLink').css('display', 'flex');

      setActiveSidebar('homePageLink');
      liveViewPageLinkClicked()
    })



  </script>
</head>

<body style="font-family: Arial, sans-serif;">
  <form method="post" id="generalForm">
    <input type="hidden" name="userName" id="userName" value="<?php echo ($_REQUEST['userName']); ?>" />
    <input type="hidden" name="userId" id="userId"
      value="<?php echo (($_REQUEST['userId'] == "") ? -1 : $_REQUEST['userId']); ?>" />
    <input type="hidden" name="userType" id="userType"
      value="<?php echo (($_REQUEST['userType'] == "") ? -1 : $_REQUEST['userType']); ?>" />
    <input type="hidden" name="token" id="token" value="<?php echo ($_REQUEST['token']); ?>" />
  </form>


  <div class="container">
    <div class="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <img src="img/SST_Logo.png" alt="Logo" />
        </div>
      </div>
      <div class="sidebar-menu">
        <!--<a class="sidebar-link active " href="javascript:void(0);" onclick="homePageLinkClicked()" id="homePageLink">
          <span class="icon">🏠</span>
          Overview
        </a> -->
        <a class="sidebar-link" href="javascript:void(0);" onclick="liveViewPageLinkClicked()" id="liveViewPageLink">
          <span class="icon">🎥</span>
          Live View
        </a>
        <!-- <a class="sidebar-link" href="javascript:void(0);" onclick="PlaybackPageLinkClicked()" id="PlaybackPageLink">
          <span class="icon">📺</span>
          Playback
        </a> -->
        <a class="sidebar-link" href="javascript:void(0);" onclick="TimeLapsePageLinkClicked()" id="TimeLapsePageLink">
          <span class="icon">⏱️</span>
          Time Lapse
        </a>
        <!-- <a class="sidebar-link" href="javascript:void(0);" onclick="DownloadPageLinkClicked()" id="DownloadPageLink">
          <span class="icon">⬇️</span>
          Download
        </a> -->
        <a class="sidebar-link" href="javascript:void(0);" onclick="EventLogPageLinkClicked()" id="EventLogPageLink">
          <span class="icon">📄</span>
          Event Log
        </a>
        <a class="sidebar-link" href="javascript:void(0);" onclick="dataAnalysisPageLinkClicked()"
          id="dataAnalysisPageLink">
          <span class="icon">📊</span>
          Data Analysis
        </a>

        <a class="sidebar-link" href="javascript:void(0);" onclick="dataAnalysisPageLink1Clicked()"
          id="dataAnalysisPageLink1">
          <span class="icon">📊</span>
          Data Analysis
        </a>
      </div>
      <div class="sidebar-footer">
        <a class="sidebar-link" href="javascript:void(0);" onclick="logoutLinkClicked()" id="logoutLink">
          <span class="icon">🚪</span>
          ➜ Logout
        </a>
      </div>
    </div>

    <div class="main-content">
      <div class="sub-content">
        <iframe id="mainFrame" name="mainFrame" style="width:100%;height:99vh;border:none;">

        </iframe>
      </div>
    </div>
  </div>
</body>

</html>
