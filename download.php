<?php 
   # the idea:
   # Part 1: time lapse video
   # 1) is to let user select a being date and end date (using html entities)
   # 2) this will send a request to the server. The server will 
   #    - start generation of lapse video in the background
   #    - return a token to the client
   # 3) the client will use the token to poll the server for status of video generation
   # 4) result is one of the following
   #    - video is ready: provide a link to download the video
   #    - video generation failed: show error message
   # 5) the UI must provide a previously downloaded video list for user to download again
   #    and if the user request the same video again, the server will return the existing video 
   #    if it is still available
   # 
   # Part 2: video download
   # 
  ?>
<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/~vidplat/current/jsModules/daterangepicker/daterangepicker.css" />

  <script src="/~vidplat/current/jsModules/jquery.min.js"></script>
  <script src="/~vidplat/current/jsModules/moment.js"></script>
  <script src="/~vidplat/current/jsModules/daterangepicker/daterangepicker.js"></script>
  <script src="jsModules/helper.js"></script>
  <script src="jsModules/constants.js?v=002"></script>

  <style>
  body {
    margin: 0;
    padding-top: 5px;
    padding-left: 5px;
    font-family: Arial, sans-serif;
    background: #f5f6f7;
    overflow-x: hidden;
  }

  .container {
    display: flex;
    flex-direction: row;
    height: 80vh;
    width: 100vw;
    box-sizing: border-box;
  }

  @media (max-width: 1180px),
  (max-height: 820px) {

    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f5f6f7;

    }

  }

  .cardDiv {
    width: 85%;
    margin-left: 5px;
    margin-top: 20px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    padding-left: 5px;
  }

  .cardDivHeader {
    background-color: #e8e8e8;
    font-size: 18px;
    font-weight: bold;
    color: #333;
    padding: 10px 10px;
    margin-left: -5px;
  }

  .cardDivBody {
    margin-left: 5px;
    margin-top: 5px;
    margin-bottom: 5px;
  }

  .dbTbl {
    border-collapse: collapse;
    bordercolor: gray;
  }

  .dbTblTd {
    padding: 3px;
  }
  </style>
  <!--
    Note: Suppose you determined .carDiv padding-left to be 5px,
    in order to make sure the header background cover the whole div :
    the .cardDivHeader margin-left -5px and .padding to XXpx 10px
  -->

  <script>
  const SITES_API_PREFIX = "/~vidplat/webapi/a.php?m=Sites"
  const DOWNLOAD_API_PREFIX = "/~vidplat/webapi/a.php?m=Download"

  const DOWNLOAD_TIMEOUT = 30;

  const DL_ST_OK = 0 // OK
  const DL_ST_DLING = 1 // downloading
  const DL_ST_UNKNOWN = -1 // unknown
  const DL_ST_TO = -2 // timeout
  const DL_ST_WEBPAGE_ERR = -3 // webpage error
  const DL_ST_DEVICE_ERR = -4 // download error

  const MAX_PREV_DOWNLOAD_TO_SHOW = 20

  const NUM_FORMAT = new Intl.NumberFormat('en-US', {
    notation: 'compact',
    compactDisplay: 'short',
    minimumFractionDigits: 0, 
    maximumFractionDigits: 2 
  });

  var userId = <?php echo (($_REQUEST['userId'] == "") ? -1 : $_REQUEST['userId']); ?>;
  var userType = <?php echo (($_REQUEST['userType'] == "") ? -1 : $_REQUEST['userType']); ?>;
  var userName = "<?php echo ($_REQUEST['userName']); ?>";
  var token = "<?php echo ($_REQUEST['token']); ?>";

  var tlDp // date range picker time-lapse
  var tlDlTkn, tlDlInt = null,
    tlDlStartTm, tlDlLastStTm, tlDlLastSt
  var tlDlPrevInfo = {
    bD: null,
    eD: null,
    siteName: null,
    dlStatus: null
  } // time lapase download prev info
  var gSiteName

  var dC = null

  function onSiteChange() {
    let newIdx = parseInt($('#siteSel').val())
    if (dC.SiteIdxCur != newIdx) {
      dC.SiteIdxCur = newIdx
      renderSites()
    }
  }

  function genPrevDownloadedTbl() {
    let prevDlData

    // Update previously downloaded videos
    prevDlData = {
      userId: userId,
      siteName: gSiteName,
      token: token
    }

    $.ajax({
      type: 'POST',
      url: `${DOWNLOAD_API_PREFIX}&a=prevDownloaded`,
      data: prevDlData,
      success: function(data) {
        // Sample data.data:
        // {
        //   "colNames": [ "genTm", "fN", "dispName", "size" ],
        //   "tl": {
        //     "dir": "/vidData/sites/BH-01/download/timelapse"
        //     "rowData": [
        //       [
        //         "2026-01-28_23-56-12-12345",
        //         "BH-01_2026-01-09_2026-01-20_(2026-01-28_23-56-12-12345).mp4"
        //       ],
        //       [
        //         "2026-01-28_22-56-12-12345",
        //         "BH-01_2026-01-08_2026-01-11_(2026-01-28_22-56-12-12345).mp4"
        //       ],
        //     ],
        //   }
        //   "vid": {
        //     "dir": "/~vidplat/vidData/download/video",
        //     "rowData": [
        //       [
        //         "2026-02-04_14-01-05-040352",
        //         "(2026-02-04_14-01-05-040352)_BH-01_2025-12-07-12-14-23_30.mp4",
        //         "BH-01_2025-12-07-12-14-23_30.mp4",
        //         0
        //       ],
        //       [
        //         "2026-02-04_14-01-05-040352",
        //         "(2026-02-04_14-01-05-040352)_BH-01_2025-12-07-12-14-23_360.mp4",
        //         "BH-01_2025-12-07-12-14-23_360.mp4",
        //         0
        //       ]
        //     ]
        //   }
        // }
        if (parseInt(data.code, 10) === 0) {
          let rowData, dir
          let ky = objFlip(data.data.colNames)

          ////////////////////////////////////////
          // generation of prevTlTbl's table body
          rowData = data.data.tl.rowData
          dir = data.data.tl.dir
          $('#prevTlTbl tbody').empty()
          for (let i = 0; i < rowData.length && i < MAX_PREV_DOWNLOAD_TO_SHOW; i++) {
            // creat a row and append to the table body
            let rowEle = $('<tr/>')
            let genTmStr = rowData[i][ky.genTm].replace(
              /^(\d{4}-\d{2}-\d{2})_(\d{2})-(\d{2})-(\d{2}).*/,
              '$1 $2:$3:$4'
            )
            rowEle.append(`<td class="dbTblTd" style="text-align:right;">${i+1}</td>`)
            rowEle.append(`<td class="dbTblTd">${genTmStr}</td>`)
            rowEle.append(
              `<td class="dbTblTd"><a href="${dir}/${rowData[i][ky.fN]}" download="${rowData[i][ky.dispName]}">${rowData[i][ky.dispName]}</a></td>`
            )
            rowEle.append(
              `<td class="dbTblTd" style="text-align:left;">${NUM_FORMAT.format(rowData[i][ky.size])}</td>`
            )
            
            rowEle.append(
                `<td class="dbTblTd" style="text-align:center;">`
              + `<a href="javascript:tlDelFile('${rowData[i][ky.fN]}', '${rowData[i][ky.dispName]}')">Delete</a>`
              + `</td>`
            )

            $('#prevTlTbl tbody').append(rowEle)

          }


          ////////////////////////////////////////
          // generation of prevVidTbl's table body
          rowData = data.data.vid.rowData
          dir = data.data.vid.dir
          $('#prevVidTbl tbody').empty()
          for (let i = 0; i < rowData.length && i < MAX_PREV_DOWNLOAD_TO_SHOW; i++) {
            // creat a row and append to the table body
            let rowEle = $('<tr/>')
            let genTmStr = rowData[i][ky.genTm].replace(
              /^(\d{4}-\d{2}-\d{2})_(\d{2})-(\d{2})-(\d{2}).*/,
              '$1 $2:$3:$4'
            )
            rowEle.append(`<td class="dbTblTd" style="text-align:right;">${i+1}</td>`)
            rowEle.append(
              `<td class="dbTblTd"><a href="${dir}/${rowData[i][ky.fN]}" download="${rowData[i][ky.dispName]}">${rowData[i][ky.dispName]}</a></td>`
            )
            rowEle.append(`<td class="dbTblTd">${genTmStr}</td>`)
            rowEle.append(
              `<td class="dbTblTd" style="text-align:left;">${NUM_FORMAT.format(rowData[i][ky.size])}</td>`
            )
            rowEle.append(
                `<td class="dbTblTd" style="text-align:center;">`
              + `<a href="javascript:vidDelFile('${rowData[i][ky.fN]}', '${rowData[i][ky.dispName]}')">Delete</a>`
              + `</td>`
            )
            $('#prevVidTbl tbody').append(rowEle)
          }

        } else {
          console.log(data)
          $('#downloadTimer').text('Failed')
        }
      },
      error: function(xhr, status, error) {
        console.log(error);
      },
      async: false,
    })
  }

  function renderSites() {
    let sKy, sgKy
    let curSGData, curSGName, sitesData, siteInfo

    // Update the site selection menu
    sKy = dC.SitesKy
    sgKy = dC.SGKy
    curSGData = dC.SGData[dC.SGIdxCur]
    curSGName = curSGData[sgKy.name]
    sitesData = dC.SGLU[curSGName]
    gSiteName = sitesData.sites[dC.SiteIdxCur]
    siteInfo = sitesData.sitesInfo[gSiteName]

    $('#siteSel').empty();
    for (i = 0; i < sitesData.sites.length; i++) {
      let siteName = sitesData.sites[i];
      let optEle = $('<option/>');
      optEle.attr('value', i.toString());
      optEle.text(siteName);
      if (i == dC.SiteIdxCur)
        optEle.attr('selected', 'selected');
      $('#siteSel').append(optEle);
    }

    $('#dispAddr').text(siteInfo[sKy.addr])
    $('#dispDist').text(DISTRICT_NAMES[curSGData[sgKy.district]])

    // Update the date picker
    const absoluteMinDate = moment(siteInfo[sKy.ssBeginTm], "YYYY-MM-DD");
    const defaultStart = moment().subtract(7, 'days').startOf('day');
    const defaultEnd = moment().endOf('day');
    const safeStart = moment.max(defaultStart, absoluteMinDate);

    tlDp.minDate = absoluteMinDate
    tlDp.maxDate = moment()

    tlDp.setStartDate(safeStart)
    tlDp.setEndDate(defaultEnd)

    tlDp.updateCalendars()
    tlDp.updateView()

    $('#dpTlDate').val(
      safeStart.format('YYYY-MM-DD') + ' → ' +
      defaultEnd.format('YYYY-MM-DD')
    );

    genPrevDownloadedTbl()
  }

  function onSGChange() {
    let newIdx = parseInt($('#sgSelect').val())
    if (dC.SGIdxCur != newIdx) {
      dC.SGIdxCur = newIdx
      dC.SiteIdxCur = 0
      renderSG()
    }
  }

  function renderSG() {
    parent.onChangeSG()
    renderSites()
  }

  function onGenTlBtnClick() {
    let sKy, sgKy
    let bD, eD
    let dlData

    if (tlDlInt) {
      alert("A time-lapse video is already being generated.")
      return false;
    }

    // Update the site selection menu
    bD = tlDp.startDate.format('YYYY-MM-DD')
    eD = tlDp.endDate.format('YYYY-MM-DD')

    if (tlDlPrevInfo.bD == bD &&
      tlDlPrevInfo.eD == eD &&
      tlDlPrevInfo.siteName == gSiteName &&
      tlDlPrevInfo.dlStatus == DL_ST_OK) {
      alert("The same download has just finished. Download will not start!");
      return
    }

    tlDlPrevInfo.bD = bD
    tlDlPrevInfo.eD = eD
    tlDlPrevInfo.siteName = gSiteName

    dlData = {
      token: token,
      siteName: gSiteName,
      bD: bD,
      eD: eD
    }

    $.ajax({
      type: 'POST',
      url: `${DOWNLOAD_API_PREFIX}&a=dlTimeLapse`,
      data: dlData,
      success: function(data) {
        if (parseInt(data.code, 10) === 0) {
          tlDlTkn = data.data.token
          $('#tlStatus').text('Downloading timelapse... token: ' + tlDlTkn)
          tlDlStartTm = Date.now()
          tlDlLastStTm = null
          tlDlTknPoll()
          tlDlInt = setInterval(tlDlTknPoll, 1000)
        } else {
          console.log(data)
          $('#downloadTimer').text('Failed')
        }
      },
      error: function(xhr, status, error) {
        console.log(error);
      },
      async: false,
    })

    return false; // prevent form submission
  }

  function tlDlTknHandleRet(tlDlTknChkData) {
    var toClearInt = true,
      statusHtml = null,
      tmDiffSt, toTryOtherDev = true

    switch (parseInt(tlDlTknChkData.dlStatus)) {
      case DL_ST_DLING:
        toClearInt = false
        if (tlDlLastStTm == null || tlDlLastSt != tlDlTknChkData.progress) {
          tlDlLastSt = tlDlTknChkData.progress
          tlDlLastStTm = Date.now()
          toTryOtherDev = false
        } else {
          tmDiffSt = ((Date.now() - tlDlLastStTm) / 1000.0)

          if (tmDiffSt > DOWNLOAD_TIMEOUT) {
            toClearInt = true
            statusHtml = 'Download failed (timeout)'
          } else {
            toTryOtherDev = false
          }
        }

        if (tlDlTknChkData.progress)
          $('#tlProgress').text("Progress: " + tlDlTknChkData.progress)
        else
          $('#tlProgress').text("")
        break
      case DL_ST_OK:
        let baseName = tlDlTknChkData.filename.replace(/.*\/\(.*\)_(.*?)$/, "$1")
        statusHtml = `<a href="${tlDlTknChkData.filename}" download="${baseName}">${baseName}</a>`;
        genPrevDownloadedTbl()
        break
      case DL_ST_UNKNOWN:
        statusHtml = 'Download failed (uknown)'
        break
      case DL_ST_TO:
        statusHtml = 'Download failed (timeout)'
        break
      case DL_ST_DEVICE_ERR:
        statusHtml = 'Download failed (device error)'
        break
      case DL_ST_WEBPAGE_ERR:
      default:
        statusHtml = 'Download failed (webpage error)'
        break
    }

    if (toClearInt) {
      $('#tlTimer').text('')
      $('#tlProgress').text('')
      clearInterval(tlDlInt)
      tlDlInt = null
    }

    if (statusHtml)
      $('#tlStatus').html(statusHtml)

    tlDlPrevInfo.dlStatus = tlDlTknChkData.dlStatus
  }

  function tlDlTknPoll() {
    var tmDiff = ((Date.now() - tlDlStartTm) / 1000.0).toFixed(1)
    const DL_TKN_CHK_DATA_ERR = 
    $('#tlTimer').text('Time elapsed: (' + tmDiff.toString() + ')')

    $.post(
      `${DOWNLOAD_API_PREFIX}&a=chkTlTkn`,
      {
        tlTkn: tlDlTkn,
        siteName: gSiteName,
        token: token
      },
      function(data) {
        if (parseInt(data.code, 10) === 0) {
          tlDlTknHandleRet(data.data)
        } else {
          tlDlTknHandleRet({dlStatus: DL_ST_WEBPAGE_ERR})
        }
      }
    )
  }

  function tlDelFile(fN, dispName) {
    if (!confirm(`Are you sure to delete the file ${dispName}?`)) {
      return
    }

    $.post(
      `${DOWNLOAD_API_PREFIX}&a=tlDelFile`, 
      {
        fN: fN,
        token: token
      },
      function(data) {
        if (parseInt(data.code, 10) === 0) {          
          genPrevDownloadedTbl()
        } else {
          console.log(data)
          alert("Failed to delete file.")
        }
      }
    )
  }

  function vidDelFile(fN, dispName) {
    if (!confirm(`Are you sure to delete the file ${dispName}?`)) {
      return
    }

    $.post(
      `${DOWNLOAD_API_PREFIX}&a=vidDelFile`, 
      {
        fN: fN,
        token: token
      },
      function(data) {
        if (parseInt(data.code, 10) === 0) {          
          genPrevDownloadedTbl()
        } else {
          console.log(data)
          alert("Failed to delete file.")
        }
      }
    )
  }


  $(document).ready(function() {
    let sgKy

    // Date range picker initilastion
    $('#dpTlDate').daterangepicker({
      opens: 'left',
      autoApply: false,
      showDropdowns: true,
      locale: {
        format: 'YYYY-MM-DD',
        separator: ' → ',
        applyLabel: 'Generate',
        cancelLabel: 'Cancel'
      }
    });

    tlDp = $('#dpTlDate').data('daterangepicker')

    // Render SiteGroup
    dC = parent.dispCtx

    sgKy = dC.SGKy
    for (i = 0; i < dC.SGData.length; i++) {
      let optEle = $('<option/>')
      optEle.attr('value', i.toString())
      optEle.text(dC.SGData[i][sgKy.title])
      if (i == dC.SGIdxCur)
        optEle.attr('selected', 'selected')
      $('#sgSelect').append(optEle)
    }

    renderSG()
  });
  </script>
</head>


<body>
  <div style="font-size: 16px; font-weight: normal; color: #444; margin-bottom: 8px;">
    Site Group:<select onChange="onSGChange()" id="sgSelect" style="font-size: 16px; margin-left: 20px;"></select>
    Site: <select onChange="onSiteChange()" id="siteSel" style="font-size: 16px; margin-left: 20px;"></select>
  </div>

  <b>District: </b><span id="dispDist"></span> <b>Address: </b><span id="dispAddr"></span>

  <div class="cardDiv">
    <div class="cardDivHeader">
      Download time-lapse videos:
    </div>
    <div class="cardDivBody">
      Generate time lapse video from:
      <input type="text" id="dpTlDate"> <!-- date picker time-lapase -->
      <button id="genTlBtn" onclick="onGenTlBtnClick()">Generate</button>
      <div>
        <span id="tlStatus" />
      </div>
      <div>
        <span id="tlTimer" />
      </div>
      <div>
        <span id="tlProgress" />
      </div>
      <hr style="margin-top: 10px; margin-bottom: 10px; border: 0; border-top: 1px solid #ccc;">
      <div>
        Previously generated time lapse video:<br />
        <table class="dlTbl" id="prevTlTbl" border="1" style="border-collapse: collapse;" bordercolor="gray">
          <thead>
            <tr>
              <th class="dbTblTd">No.</th>
              <th class="dbTblTd">Generate Time</th>
              <th class="dbTblTd">Filename</th>
              <th class="dbTblTd">Size</th>
              <th class="dbTblTd">Delete</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="cardDiv">
    <div class="cardDivHeader">
      Download videos:
    </div>
    <div class="cardDivBody">

      <table style="border-collapse: collapse; border: none;">
        <tr>
          <td style="text-align: right; vertical-align: top;">Begin time:</td>
          <td style="vertical-align: top;"><input type="text" id="btActual" value="" /></td>
          <td>format: <br />
            YYYY-M-D_h-m-s (e.g. 2022-04-17_14-22-00, exact begin time)
          </td>
        </tr>
        <tr>
          <td style="text-align: right; vertical-align: top;">End time/video length:</td>
          <td style="vertical-align: top;"><input type="text" id="etActual" value="" /></td>
          <td>format:<br />
            YYYY-M-D_h-m-s (e.g. 2022-04-17_14-23-00, exact end time)<br />
            h:m:s (e.g. 1:34:24 for length of 1h34m24s)<br />
            m:s (e.g. 30:00 for length of 30m)<br />
            s (e.g. 60 for length of 60s)<br />
          </td>
        </tr>
        <tr>
          <td></td>
          <td style="text-align: center;">
            <input type="button" id="mySubmit" value="Download!" />
          </td>
          <td></td>
        </tr>
      </table>
      <div>
        <span id="vidStatus" />
      </div>
      <div>
        <span id="vidTimer" />
      </div>
    </div>
    <div>
      <span id="vidProgress" />
    </div>
    <hr style="margin-top: 10px; margin-bottom: 10px; border: 0; border-top: 1px solid #ccc;">
    <div>
      Previously downloaded video:<br />
      <table class="dlTbl" id="prevVidTbl" border="1" style="border-collapse: collapse;" bordercolor="gray">
        <thead>
          <tr>
            <th class="dbTblTd">No.</th>
            <th class="dbTblTd">Filename</th>
            <th class="dbTblTd">Generate Time</th>
            <th class="dbTblTd">Size</th>
            <th class="dbTblTd">Delete</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
    </div>
  </div>

</body>

</html>
