/**
 * Escape a string for CSV (wrap in quotes, escape inner quotes).
 */
function escapeCsv(value) {
  const str = String(value ?? '')
  return `"${str.replace(/"/g, '""')}"`
}

/**
 * Download an array of objects as a CSV file.
 *
 * @param {Object[]} data - Array of row objects.
 * @param {string[]} headers - Column headers.
 * @param {function[]} fields - Functions extracting each column value from a row.
 * @param {string} filename - Output filename.
 *
 * @example
 * exportCsv(reservas,
 *   ['Sala', 'Usuario', 'Fecha'],
 *   [r => r.space_name, r => r.user_name, r => r.start_time],
 *   'reservas.csv'
 * )
 */
export function exportCsv(data, headers, fields, filename = 'export.csv') {
  if (!data.length) return

  const headerRow = headers.map(h => escapeCsv(h)).join(',')
  const dataRows = data.map(row =>
    fields.map(fn => escapeCsv(fn(row))).join(',')
  )
  const csv = [headerRow, ...dataRows].join('\n')

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = filename
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}
