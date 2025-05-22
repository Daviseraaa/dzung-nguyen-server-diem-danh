export async function readExcelFile(file) {
    try {
        const reader = new FileReader();
        return new Promise((resolve, reject) => {
            reader.onload = (e) => {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const sheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[sheetName];
                const json = XLSX.utils.sheet_to_json(worksheet);
                resolve(json);
            };
            reader.onerror = () => reject(new Error('Không thể đọc file Excel'));
            reader.readAsArrayBuffer(file);
        });
    } catch (error) {
        console.error('Error reading Excel file:', error);
        throw error;
    }
}