import { jsPDF } from 'jspdf'

export const generateBulletinPdf = (studentName, className, grades = []) => {
  const doc = new jsPDF()

  // Header Banner
  doc.setFillColor(4, 120, 87) // emerald green
  doc.rect(0, 0, 210, 40, 'F')

  doc.setTextColor(255, 255, 255)
  doc.setFontSize(20)
  doc.setFont('helvetica', 'bold')
  doc.text('ÉCOLE TIJANES NOURS', 15, 22)

  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.text('BULLETIN SCOLAIRE TRIMESTRIEL - ANNEE 2026-2027', 15, 32)

  // Student Info Box
  doc.setTextColor(30, 41, 59)
  doc.setFontSize(12)
  doc.setFont('helvetica', 'bold')
  doc.text(`Eleve : ${studentName}`, 15, 55)
  doc.text(`Classe : ${className}`, 15, 63)
  doc.setFont('helvetica', 'normal')
  doc.setFontSize(10)
  doc.text('Trimestre : 1er Trimestre', 140, 55)
  doc.text('Assiduite : 98% (1 Absence)', 140, 63)

  // Line separator
  doc.setDrawColor(226, 232, 240)
  doc.setLineWidth(0.5)
  doc.line(15, 70, 195, 70)

  // Table Header
  doc.setFillColor(241, 245, 249)
  doc.rect(15, 75, 180, 10, 'F')
  doc.setFont('helvetica', 'bold')
  doc.text('Matiere', 20, 81)
  doc.text('Note / 20', 100, 81)
  doc.text('Appreciation de l\'Enseignant', 135, 81)

  // Default grades if empty
  const defaultGrades = grades.length > 0 ? grades : [
    { subject: 'Langue Arabe - Lecture & Ecriture', score: '18.5/20', comment: 'Excellente participation et lecture fluide.' },
    { subject: 'Saint Coran - Recitation & Tajwid', score: '19/20', comment: 'Memorisation parfaite des 12 courtes sourates.' },
    { subject: 'Education Ethique & Valeurs', score: '18/20', comment: 'Comportement exemplaire en classe.' }
  ]

  let y = 92
  defaultGrades.forEach(g => {
    doc.setFont('helvetica', 'bold')
    doc.text(g.subject, 20, y)
    doc.setTextColor(4, 120, 87)
    doc.text(g.score, 100, y)
    doc.setTextColor(30, 41, 59)
    doc.setFont('helvetica', 'normal')
    doc.text(g.comment, 135, y)
    y += 12
  })

  // Summary box
  doc.setFillColor(236, 253, 245)
  doc.rect(15, y + 10, 180, 25, 'F')
  doc.setFont('helvetica', 'bold')
  doc.setTextColor(4, 120, 87)
  doc.text('Moyenne Generale : 18.5 / 20', 25, y + 22)
  doc.text('Mention : Félicitations du Conseil de Classe', 25, y + 30)

  // Signature Block
  doc.setTextColor(100, 116, 139)
  doc.setFontSize(9)
  doc.text('Cachet de l\'Etablissement & Signature du Directeur', 120, y + 55)
  doc.setFont('helvetica', 'bold')
  doc.text('Tijanes Nours Luxembourg ASBL', 120, y + 62)

  doc.save(`Bulletin_${studentName.replace(/\s+/g, '_')}_T1.pdf`)
}

export const generateInvoicePdf = (parentName, childName, amount = 690) => {
  const doc = new jsPDF()

  // Header Banner
  doc.setFillColor(30, 58, 138) // dark blue
  doc.rect(0, 0, 210, 40, 'F')

  doc.setTextColor(255, 255, 255)
  doc.setFontSize(20)
  doc.setFont('helvetica', 'bold')
  doc.text('ÉCOLE TIJANES NOURS', 15, 22)

  doc.setFontSize(10)
  doc.setFont('helvetica', 'normal')
  doc.text('FACTURE / ATTESTATION DE PAIEMENT ACQUITTEE', 15, 32)

  // Invoice Details
  doc.setTextColor(30, 41, 59)
  doc.setFontSize(10)
  doc.setFont('helvetica', 'bold')
  doc.text(`Facture N° : FACT-2026-0089`, 15, 55)
  doc.text(`Date : ${new Date().toLocaleDateString('fr-FR')}`, 15, 62)

  doc.text(`Responsable Legal : ${parentName}`, 120, 55)
  doc.text(`Eleve Concerne : ${childName}`, 120, 62)

  // Table
  doc.setFillColor(241, 245, 249)
  doc.rect(15, 75, 180, 10, 'F')
  doc.text('Designation', 20, 81)
  doc.text('Periode', 110, 81)
  doc.text('Montant EUR', 160, 81)

  doc.setFont('helvetica', 'normal')
  doc.text(`Cotisation Scolaire Annuelle - ${childName}`, 20, 93)
  doc.text('Année 2026-2027', 110, 93)
  doc.text(`${amount}.00 EUR`, 160, 93)

  // Line
  doc.setDrawColor(226, 232, 240)
  doc.line(15, 105, 195, 105)

  // ISO 11649 Ref
  doc.setFillColor(239, 246, 255)
  doc.rect(15, 115, 180, 25, 'F')
  doc.setFont('helvetica', 'bold')
  doc.setTextColor(29, 78, 216)
  doc.text('Reference de Virement Structurée ISO 11649 :', 20, 125)
  doc.setFontSize(14)
  doc.text('RF12 2026 0042 89', 20, 134)

  // Total
  doc.setFontSize(12)
  doc.setTextColor(4, 120, 87)
  doc.text(`Total Reglé : ${amount}.00 EUR (Acquitté)`, 120, 155)

  doc.save(`Facture_${childName.replace(/\s+/g, '_')}_2026.pdf`)
}
