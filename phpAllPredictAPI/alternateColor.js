// Function ตรวจหาการสลับสีและเพิ่ม field ลงใน data
function addColorAlternationFields(data, minAlternationCount = 3) {
  if (!data || data.length < 2) {
    return data;
  }

  const result = [...data];

  // เพิ่ม field สำหรับแต่ละ item
  result.forEach(item => {
    item.colorAlternationInfo = {
      isInAlternationPattern: false,
      alternationLength: 0,
      alternationStartTime: null,
      alternationStartIndex: null,
      patternType: null, // 'ongoing' หรือ 'completed'
      expectedNextColor: null
    };
  });

  // ตรวจหาการสลับสี
  for (let i = minAlternationCount; i < result.length; i++) {
    // ตรวจสอบ pattern สลับสี โดยดูย้อนหลัง
    let alternationLength = findAlternationLength(result, i, minAlternationCount);

    if (alternationLength >= minAlternationCount) {
      const startIndex = i - alternationLength + 1;
      const startTime = result[startIndex].time;

      // อัพเดต field สำหรับทุก item ใน alternation pattern
      for (let j = startIndex; j <= i; j++) {
        result[j].colorAlternationInfo = {
          isInAlternationPattern: true,
          alternationLength: alternationLength,
          alternationStartTime: startTime,
          alternationStartIndex: startIndex,
          patternType: j === i ? 'ongoing' : 'completed',
          expectedNextColor: j === i ? getExpectedNextColor(result, j) : null
        };
      }
    }
  }

  return result;
}

// Function หาความยาวของการสลับสี ณ จุดที่กำหนด
function findAlternationLength(data, endIndex, minLength) {
  if (endIndex < minLength - 1) return 0;

  let length = 1;
  let currentColor = data[endIndex].thisColor;

  // ดูย้อนหลังเพื่อหาการสลับสี
  for (let i = endIndex - 1; i >= 0; i--) {
    const prevColor = data[i].thisColor;

    if (prevColor !== currentColor) {
      length++;
      currentColor = prevColor;
    } else {
      // ถ้าสีเดียวกัน แสดงว่าการสลับสีหยุด
      break;
    }
  }

  return length;
}

// Function หาสีที่คาดว่าจะมาถัดไป
function getExpectedNextColor(data, currentIndex) {
  if (currentIndex < 1) return null;

  const currentColor = data[currentIndex].thisColor;
  return currentColor === 'Green' ? 'Red' : 'Green';
}

// Function สร้างรายงานสรุปการสลับสี
function generateAlternationReport(data, minAlternationCount = 3) {
  const dataWithAlternation = addColorAlternationFields(data, minAlternationCount);

  // หา pattern ที่ unique
  const alternationPatterns = [];
  const processedStartTimes = new Set();

  dataWithAlternation.forEach((item, index) => {
    const info = item.colorAlternationInfo;

    if (info.isInAlternationPattern &&
        info.alternationStartTime &&
        !processedStartTimes.has(info.alternationStartTime)) {

      processedStartTimes.add(info.alternationStartTime);

      // หาข้อมูลของ pattern นี้
      const patternItems = dataWithAlternation.filter(d =>
        d.colorAlternationInfo.alternationStartTime === info.alternationStartTime
      );

      const pattern = {
        startTime: info.alternationStartTime,
        startIndex: info.alternationStartIndex,
        length: info.alternationLength,
        endTime: patternItems[patternItems.length - 1].time,
        endIndex: info.alternationStartIndex + info.alternationLength - 1,
        colorSequence: patternItems.map(p => p.thisColor),
        timeSequence: patternItems.map(p => ({
          time: p.time,
          color: p.thisColor,
          timestamp: new Date(p.time * 1000).toISOString()
        })),
        isOngoing: patternItems.some(p => p.colorAlternationInfo.patternType === 'ongoing'),
        expectedNextColor: patternItems.find(p => p.colorAlternationInfo.expectedNextColor)?.colorAlternationInfo.expectedNextColor
      };

      alternationPatterns.push(pattern);
    }
  });

   console.log('New ',alternationPatterns)

  // สร้างรายงานสรุป
  const report = {
    totalAlternationPatterns: alternationPatterns.length,
    minAlternationLength: minAlternationCount,
    patterns: alternationPatterns,
    summary: {
      longestPattern: alternationPatterns.length > 0 ?
        Math.max(...alternationPatterns.map(p => p.length)) : 0,
      averageLength: alternationPatterns.length > 0 ?
        (alternationPatterns.reduce((sum, p) => sum + p.length, 0) / alternationPatterns.length).toFixed(2) : 0,
      ongoingPatterns: alternationPatterns.filter(p => p.isOngoing).length
    }
  };

  // สร้าง HTML Table
  const htmlTable = generateHTMLTable(report, minAlternationCount);

  return {
    dataWithAlternation: dataWithAlternation,
    report: report,
    htmlTable: htmlTable
  };
}

// Function สร้าง HTML Table
function generateHTMLTable(report, minAlternationCount) {
  if (!report.patterns || report.patterns.length === 0) {
    return `
      <div style="padding: 20px; text-align: center; background-color: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
        <h3 style="color: #6c757d; margin: 0;">ไม่พบรูปแบบการสลับสี</h3>
        <p style="color: #868e96; margin: 10px 0 0 0;">ไม่มีการสลับสี ${minAlternationCount} ครั้งขึ้นไปในข้อมูลนี้</p>
      </div>
    `;
  }

  let html = `
    <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
      <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px 8px 0 0; margin-bottom: 0;">
        <h3 style="margin: 0; font-size: 18px;">📊 รายงานการสลับสี</h3>
        <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;">พบ ${report.totalAlternationPatterns} รูปแบบ (ขั้นต่ำ ${minAlternationCount} ครั้ง)</p>


      <div style="background-color: #f8f9fa; padding: 10px 15px; border: 1px solid #dee2e6; display: flex; gap: 20px; font-size: 14px;">
        <span><strong>รูปแบบยาวสุด:</strong> <span style="color: #28a745;">${report.summary.longestPattern} ครั้ง</span></span>
        <span><strong>ความยาวเฉลี่ย:</strong> <span style="color: #17a2b8;">${report.summary.averageLength} ครั้ง</span></span>
        <span><strong>กำลังเกิด:</strong> <span style="color: #fd7e14;">${report.summary.ongoingPatterns} รูปแบบ</span></span>
      </div>
     </div>
  `;

  html += `<div id="containerA" style="border:2px solid red;" >
	  <button type="button" onclick=toggleContainerHeight('containerA')>แสดง ตาราง alter color </button>
      <table id='alterColorTable' style="width: 100%; border-collapse: collapse; border: 1px solid #dee2e6; background-color: white;">
        <thead>
          <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-bottom: 2px solid #dee2e6;">
            <th style="padding: 12px 8px; text-align: center; border-right: 1px solid #dee2e6; font-weight: 600; color: #ffffff;">#</th>
            <th style="padding: 12px 8px; text-align: center; border-right: 1px solid #dee2e6; font-weight: 600; color: #ffffff;">ความยาว</th>
	        <th style="padding: 12px 8px; text-align: center; border-right: 1px solid #dee2e6; font-weight: 600; color: #ffffff;">TimeStamp </th>
            <th style="padding: 12px 8px; text-align: center; border-right: 1px solid #dee2e6; font-weight: 600; color: #ffffff;">เวลาเริ่ม</th>
            <th style="padding: 12px 8px; text-align: center; border-right: 1px solid #dee2e6; font-weight: 600; color: #ffffff;">เวลาจบ</th>
            <th style="padding: 12px 8px; text-align: center; border-right: 1px solid #dee2e6; font-weight: 600; color: #ffffff;">ลำดับสี</th>
            <th style="padding: 12px 8px; text-align: center; border-right: 1px solid #dee2e6; font-weight: 600; color: #ffffff;">สถานะ</th>
            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #ffffff;">คาดการณ์</th>
	        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #ffffff;">Copy</th>
          </tr>
        </thead>
        <tbody>
  `;

  report.patterns.forEach((pattern, index) => {
    const rowBg = index % 2 === 0 ? '#ffffff' : '#f8f9fa';
    const statusColor = pattern.isOngoing ? '#fd7e14' : '#28a745';
    const statusText = pattern.isOngoing ? '🔄 กำลังเกิด' : '✅ สมบูรณ์';

    // สร้าง color sequence แสดงผล
    const colorSequence = pattern.colorSequence.map(color => {
      const bgColor = color === 'Green' ? '#d4edda' : '#f8d7da';
      const textColor = color === 'Green' ? '#155724' : '#721c24';
      const emoji = color === 'Green' ? '🟢' : '🔴';
      return `<span style="display: inline-block; padding: 2px 6px; margin: 1px; background-color: ${bgColor}; color: ${textColor}; border-radius: 12px; font-size: 12px; font-weight: 500;">${emoji}</span>`;
    }).join('');

    const expectedColor = pattern.expectedNextColor ?
      `<span style="padding: 4px 8px; background-color: ${pattern.expectedNextColor === 'Green' ? '#d4edda' : '#f8d7da'}; color: ${pattern.expectedNextColor === 'Green' ? '#155724' : '#721c24'}; border-radius: 12px; font-size: 12px; font-weight: 500;">
        ${pattern.expectedNextColor === 'Green' ? '🟢 เขียว' : '🔴 แดง'}
      </span>` :
      '<span style="color: #6c757d; font-style: italic;">-</span>';

    html += `
          <tr id="alterRow_${index+1}" style="background-color: ${rowBg}; border-bottom: 1px solid #dee2e6;">
            <td style="color:black;padding: 10px 8px; text-align: center; border-right: 1px solid #dee2e6; font-weight: 600; color: black;">${index + 1}</td>
            <td style="padding: 10px 8px; text-align: center; border-right: 1px solid #dee2e6;">
              <span style="background-color: #e3f2fd; color: #1565c0; padding: 4px 8px; border-radius: 12px; font-weight: 600; font-size: 13px;">
                ${pattern.length} ครั้ง
              </span>
            </td>
		    <td style="padding: 10px 8px; text-align: center; border-right: 1px solid #dee2e6; font-size: 12px; color: black;">
              ${pattern.startTime}
            </td>
            <td style="padding: 10px 8px; text-align: center; border-right: 1px solid #dee2e6; font-size: 12px; color: black;">
              ${new Date(pattern.startTime * 1000).toISOString().slice(0, 16).replace('T', ' ')}
            </td>
            <td style="padding: 10px 8px; text-align: center; border-right: 1px solid #dee2e6; font-size: 12px; color: black;">
              ${new Date(pattern.endTime * 1000).toISOString().slice(0, 16).replace('T', ' ')}
            </td>
            <td style="padding: 10px 8px; text-align: center; border-right: 1px solid #dee2e6;">
              ${colorSequence}
            </td>
            <td style="padding: 10px 8px; text-align: center; border-right: 1px solid #dee2e6;">
              <span style="color: ${statusColor}; font-weight: 600; font-size: 13px;">${statusText}</span>
            </td>
            <td style="padding: 10px 8px; text-align: center;">
              ${expectedColor}
            </td>
            <td style="padding: 10px 8px; text-align: center;">
              <button type="button" id='' class="mBtn" onclick="doCopyDataInterval(${pattern.startTime},${pattern.endTime})">Copy Data</button>
			  <button type="button" id='' class="mBtn" onclick="doCopyDataIntervalOnNext(${pattern.startTime},${pattern.endTime})">Copy Data Next 10</button>

            </td>
          </tr>
    `;
  });


  html += `
        </tbody>
      </table>
	  </div>
    </div>
  `;

  return html;
}


function doCopyDataInterval(startEpoch,endEpoch) {

	     rawData = JSON.parse(document.getElementById("rawCandle").value) ;
		 console.clear() ;
		 console.log(rawData)

         console.log(startEpoch,endEpoch);

		 //startEpoch = 1753218120 ;
         //endEpoch = 1753219440 ;

         startEpoch = startEpoch - (7*3600) ;
		 endEpoch   = endEpoch - (7*3600) ;


		 //const result = rawData.filter(object => (object.epoch >= startTime) && (object.epoch <= endTime));
		 const filtered = rawData.filter(item =>
            item.epoch >= startEpoch && item.epoch <= endEpoch
         );


		 console.log('Copy Result ',JSON.stringify(filtered)) ;

		 // 🔹 แปลงเป็น JSON text สวยงาม
         const textToCopy = JSON.stringify(filtered, null, 2);
		  // 🔹 คัดลอกไป clipboard
         navigator.clipboard.writeText(textToCopy)
         .then(() => alert("Copied data to clipboard!"))
         .catch(err => console.error("Copy failed:", err));


} // end func

function doCopyDataIntervalOnNext(startEpoch,endEpoch) {

	     rawData = JSON.parse(document.getElementById("rawCandle").value) ;
		 console.clear() ;
		 console.log(rawData)

         console.log(startEpoch,endEpoch);

		 const minutesToAdd = 30;
         const secondsToAdd = minutesToAdd * 60;
		 endEpoch2 = endEpoch + secondsToAdd;


		 //startEpoch = 1753218120 ;
         //endEpoch = 1753219440 ;

         startEpoch = startEpoch - (7*3600) ;
		 endEpoch   = endEpoch - (7*3600) ;
		 endEpoch2   = endEpoch2 - (7*3600) ;


		 //const result = rawData.filter(object => (object.epoch >= startTime) && (object.epoch <= endTime));
		 const filtered = rawData.filter(item =>
            item.epoch >= startEpoch && item.epoch <= endEpoch2
         );


		 console.log('Copy Result ',JSON.stringify(filtered)) ;

		 // 🔹 แปลงเป็น JSON text สวยงาม
         const textToCopy = JSON.stringify(filtered, null, 2);
		  // 🔹 คัดลอกไป clipboard
         navigator.clipboard.writeText(textToCopy)
         .then(() => alert("Copied data to clipboard!"))
         .catch(err => console.error("Copy failed:", err));


} // end func




function toggleContainerHeight(containerId) {

    if ($("#alterColorTable").hasClass('hide')) {
		$("#alterColorTable").removeClass('hide');
    } else {
       $("#alterColorTable").addClass('hide');
	}
	/*
    const container = document.getElementById(containerId);

    const isCollapsed = container.style.height === '1000px' || container.style.height === '';

    if (isCollapsed) {
        container.style.height = 'auto'; // Expand to full height
        container.style.overflow = 'visible';
    } else {
        container.style.height = '1000px'; // Collapse to fixed height
        container.style.overflow = 'hidden';
    }
	*/
}

/*
// ตัวอย่างการใช้งาน
const sampleData = [
  {
    "time": 1750997160,
    "open": 99427.1961,
    "high": 99579.3184,
    "low": 99385.5709,
    "close": 99579.3184,
    "thisColor": "Green"
  },
  {
    "time": 1750997220,
    "open": 99579.3184,
    "high": 99600.0000,
    "low": 99500.0000,
    "close": 99520.0000,
    "thisColor": "Red"
  },
  {
    "time": 1750997280,
    "open": 99520.0000,
    "high": 99650.0000,
    "low": 99510.0000,
    "close": 99630.0000,
    "thisColor": "Green"
  },
  {
    "time": 1750997340,
    "open": 99630.0000,
    "high": 99640.0000,
    "low": 99580.0000,
    "close": 99590.0000,
    "thisColor": "Red"
  }
];

// เรียกใช้งาน
// const result = generateAlternationReport(sampleData, 3);
// console.log('Data with alternation fields:', result.dataWithAlternation);
// console.log('Alternation report:', result.report);

// หรือใช้แค่เพิ่ม field อย่างเดียว
// const dataWithFields = addColorAlternationFields(sampleData, 3);
// console.log('Data with fields:', dataWithFields);

*/