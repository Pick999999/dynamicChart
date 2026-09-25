// trader1.runTrade(0)
 //determineAction(currentIndex,lossContinue)
function Check4(color,preViousColor1,preViousColor2,preViousColor3,preViousColor4) {
if (
	(color != preViousColor1) &&
	(preViousColor1 == preViousColor2)

) {
				console.log('-----------');

				let action = '???';
				if (color == 'Red') {
					action =  'CALL';
				}
				if (color == 'Green') {
					action = 'PUT';
				}
				return action;

	} else {
      return '';
	}

} // end func


function adjustDateTime2(id,direction, unit) {
    const input = document.getElementById(id);
    let currentValue;

    // พยายาม parse ค่า input (อาจเป็น MM/DD/YYYY hh:mm AM/PM หรือ ISO format)
    try {
        // ลอง parse รูปแบบ MM/DD/YYYY hh:mm AM/PM
        const regex = /^(\d{2})\/(\d{2})\/(\d{4})\s(\d{1,2}):(\d{2})\s(AM|PM)$/;
        const match = input.value.match(regex);
        if (match) {
            let [, month, day, year, hours, minutes, period] = match;
            hours = parseInt(hours);
            if (period === 'PM' && hours !== 12) hours += 12;
            if (period === 'AM' && hours === 12) hours = 0;
            currentValue = new Date(year, month - 1, day, hours, minutes);
        } else {
            // ลอง parse รูปแบบ ISO (YYYY-MM-DDThh:mm)
            currentValue = new Date(input.value);
        }
    } catch (e) {
        console.error('Invalid date format');
        return;
    }

    if (isNaN(currentValue)) return; // ตรวจสอบวันที่ไม่ถูกต้อง

    // ปรับวันที่หรือเวลา
    if (unit === 'day') {
        currentValue.setDate(currentValue.getDate() + (direction === '+' ? 1 : -1));
    } else if (unit === 'minute') {
        currentValue.setMinutes(currentValue.getMinutes() + (direction === '+' ? 1 : -1));
    }

    // แปลงเป็น format YYYY-MM-DDThh:mm สำหรับ datetime-local
    const year = currentValue.getFullYear();
    const month = String(currentValue.getMonth() + 1).padStart(2, '0');
    const day = String(currentValue.getDate()).padStart(2, '0');
    const hours = String(currentValue.getHours()).padStart(2, '0');
    const minutes = String(currentValue.getMinutes()).padStart(2, '0');
    input.value = `${year}-${month}-${day}T${hours}:${minutes}`;

    SaveLocal(); // เรียกฟังก์ชัน SaveLocal()
}


function generateTableTrade(inputName) {

            const input = document.getElementById(inputName).value;
            let data;
            try {
                data = JSON.parse(input);
                if (!Array.isArray(data) || data.length === 0) {
                    alert('Please enter a valid JSON array with data.');
                    return;
                }
            } catch (e) {
                alert('Invalid JSON format.');
                return;
            }

            originalData = data;
            const container = document.getElementById('resultsContainer');
            container.innerHTML = '';

            const table = document.createElement('table');
            table.id = 'dataTable';
            const headerRow = document.createElement('tr');
            const filterRow = document.createElement('tr');
            const headers = Object.keys(data[0]);
            const filters = {};

            // Populate column select dropdown
            const columnSelect = document.getElementById('columnSelect');
            columnSelect.innerHTML = '<option value="">Select Column</option>';
            headers.forEach(header => {
                const option = document.createElement('option');
                option.value = header;
                option.textContent = header;
                columnSelect.appendChild(option);
            });

            // Create header and filter inputs
            headers.forEach(header => {
                const th = document.createElement('th');
                th.textContent = header;
                headerRow.appendChild(th);

                const filterTh = document.createElement('th');
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'filter';
                input.placeholder = `Filter ${header}`;
                input.oninput = () => {
                    filters[header] = input.value.toLowerCase();
					//scrollToMatch(filters, headers);
                    filterTable(filters, headers);
                };
                filterTh.appendChild(input);
                filterRow.appendChild(filterTh);
            });

            table.appendChild(headerRow);
            table.appendChild(filterRow);

            // Create tbody
            const tbody = document.createElement('tbody');
            table.appendChild(tbody);
            container.appendChild(table);

            // Initial table population
            populateTable(data, tbody, headers);

            // Store original data for filtering
            table.dataset.originalData = JSON.stringify(data);
        }

        function populateTable(data, tbody, headers) {
            tbody.innerHTML = '';
            data.forEach(item => {
                const row = document.createElement('tr');
                headers.forEach(header => {
                    const cell = document.createElement('td');
                    cell.textContent = item[header] ?? '';
                    row.appendChild(cell);
                });
                tbody.appendChild(row);
            });
        }

        function filterTable(filters, headers) {
            const table = document.getElementById('dataTable');
            const tbody = table.getElementsByTagName('tbody')[0];
            const data = JSON.parse(table.dataset.originalData);
            console.log('Filters',filters)

            const filteredData = data.filter(item =>
                headers.every(header =>
                    !filters[header] || String(item[header]).toLowerCase().includes(filters[header])
                )
            );
            console.log('Filter Data',filteredData)
            populateTable(filteredData, tbody, headers);
        }

		function scrollToMatch(filters, headers) {
			const table = document.getElementById('dataTable');
			const tbody = table.getElementsByTagName('tbody')[0];
			const rows = tbody.getElementsByTagName('tr');

			// loop row ทั้งหมด
			for (let i = 0; i < rows.length; i++) {
				const row = rows[i];
				const cells = row.getElementsByTagName('td');

				// check เงื่อนไขว่า row นี้ match filters ไหม
				let isMatch = headers.every((header, index) => {
					if (!filters[header]) return true; // ไม่มี filter -> ผ่าน
					const cellValue = String(cells[index].textContent).toLowerCase();
					return cellValue.includes(filters[header]);
				});

				if (isMatch) {
					// scroll ไปที่ row ที่เจอ
					row.scrollIntoView({ behavior: "smooth", block: "center" });
					row.style.backgroundColor = "#ffeb3b"; // highlight สีเหลือง
					setTimeout(() => row.style.backgroundColor = "", 2000); // เอาสีออกหลัง 2 วิ
					break; // เจอแล้วหยุด
				}
			}
		}


        function showMaxValues() {
            const table = document.getElementById('dataTable');
            if (!originalData.length || !table) {
                alert('Please generate a table first.');
                return;
            }

            const selectedColumn = document.getElementById('columnSelect').value;
            if (!selectedColumn) {
                alert('Please select a column.');
                return;
            }

            const tbody = table.getElementsByTagName('tbody')[0];
            const headers = Object.keys(originalData[0]);
            const maxRow = document.createElement('tr');
            maxRow.className = 'max-row';

            headers.forEach(header => {
                const cell = document.createElement('td');
                if (header === selectedColumn) {
                    const values = originalData.map(item => {
                        const value = item[header];
                        return isNaN(value) ? value : Number(value);
                    });
                    let maxValue;
                    if (values.every(v => isNaN(v))) {
                        // Handle non-numeric values
                        maxValue = values.sort().slice(-1)[0];
                    } else {
                        // Handle numeric values
                        maxValue = Math.max(...values.filter(v => !isNaN(v)));
                    }
                    cell.textContent = maxValue ?? 'N/A';
                } else {
                    cell.textContent = '';
                }
                maxRow.appendChild(cell);
            });

            // Remove existing max row if present
            const existingMaxRow = tbody.querySelector('.max-row');
            if (existingMaxRow) existingMaxRow.remove();

            // Append max row at the top
            tbody.insertBefore(maxRow, tbody.firstChild);

            // Scroll to the top of the table
            document.getElementById('tableContainer').scrollTop = 0;
        }