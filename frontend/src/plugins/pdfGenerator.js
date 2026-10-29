import { jsPDF } from 'jspdf'
import html2canvas from 'html2canvas'

export const generateBulletinPdf = (studentName, className, evaluationData = null) => {
  const doc = new jsPDF()

  const term = evaluationData?.term || 'Trimestre 1'
  const schoolYear = evaluationData?.schoolYear || '2026-2027'
  const remarks = evaluationData?.teacherRemarks || evaluationData?.appreciation || 'Très bon trimestre, élève attentif, motivé et régulier dans son apprentissage.'
  const avgStars = evaluationData?.averageStars || '4.8'

  // Header Banner
  doc.setFillColor(4, 120, 87) // emerald green
  doc.rect(0, 0, 210, 38, 'F')

  doc.setTextColor(255, 255, 255)
  doc.setFontSize(18)
  doc.setFont('helvetica', 'bold')
  doc.text('ÉCOLE TIJANES NOURS LUXEMBOURG', 15, 18)

  doc.setFontSize(9)
  doc.setFont('helvetica', 'normal')
  doc.text(`BULLETIN SCOLAIRE OFFICIEL PAR COMPÉTENCES - ANNÉE ${schoolYear}`, 15, 28)

  // Student Info Box
  doc.setTextColor(30, 41, 59)
  doc.setFontSize(11)
  doc.setFont('helvetica', 'bold')
  doc.text(`Élève : ${studentName}`, 15, 48)
  doc.setFont('helvetica', 'normal')
  doc.setFontSize(9)
  doc.text(`Classe : ${className}`, 15, 54)
  doc.text(`Adresse : 32, Rue Dernier Sol, L-2543 Luxembourg`, 15, 60)

  doc.setFont('helvetica', 'bold')
  doc.setFontSize(10)
  doc.text(`Période : ${term}`, 135, 48)
  doc.setFont('helvetica', 'normal')
  doc.setFontSize(9)
  doc.text(`Enseignant : Cheikh Mahmoud`, 135, 54)
  doc.text(`Moyenne globale : ${avgStars} / 5 étoiles`, 135, 60)

  // Line separator
  doc.setDrawColor(203, 213, 225)
  doc.setLineWidth(0.5)
  doc.line(15, 66, 195, 66)

  // Table Header
  doc.setFillColor(241, 245, 249)
  doc.rect(15, 70, 180, 8, 'F')
  doc.setFont('helvetica', 'bold')
  doc.setFontSize(9)
  doc.text('Matières & Compétences Évaluées', 20, 75.5)
  doc.text('Niveau d\'Acquisition (1 à 5 ⭐)', 125, 75.5)

  // Build items list
  let items = []
  if (evaluationData?.curriculum && Array.isArray(evaluationData.curriculum)) {
    evaluationData.curriculum.forEach(sub => {
      sub.skills?.forEach(sk => {
        const rating = evaluationData.ratings ? (evaluationData.ratings[sk.id] || 5) : 5
        items.push({
          subject: sub.name,
          skill: sk.name,
          stars: rating
        })
      })
    })
  }

  if (items.length === 0) {
    items = [
      { subject: 'Saint Coran', skill: 'Mémorisation des sourates', stars: 5 },
      { subject: 'Éducation Islamique', skill: 'Acquisition des concepts religieux prescrits', stars: 5 },
      { subject: 'Lecture', skill: 'Lecture fluide avec le respect de la ponctuation', stars: 4 },
      { subject: 'Lecture', skill: 'Compréhension', stars: 5 },
      { subject: 'Expression Orale', skill: 'Mémoriser les dialogues', stars: 4 },
      { subject: 'Expression Orale', skill: 'Mémoriser le vocabulaire', stars: 5 },
      { subject: 'Expression Orale', skill: 'Former des phrases avec les mots acquis', stars: 4 },
      { subject: 'Expression Orale', skill: 'Compréhension orale', stars: 5 },
      { subject: 'Dictée', skill: 'Écrire correctement les mots dictés', stars: 5 }
    ]
  }

  let y = 84
  let currentSubject = ''

  items.forEach(item => {
    // Subject header line if changed
    if (item.subject !== currentSubject) {
      currentSubject = item.subject
      doc.setFillColor(248, 250, 252)
      doc.rect(15, y - 4, 180, 6, 'F')
      doc.setFont('helvetica', 'bold')
      doc.setFontSize(8.5)
      doc.setTextColor(4, 120, 87)
      doc.text(currentSubject.toUpperCase(), 18, y)
      y += 6.5
    }

    doc.setFont('helvetica', 'normal')
    doc.setFontSize(8)
    doc.setTextColor(30, 41, 59)
    doc.text(`• ${item.skill}`, 22, y)

    // Star display representation
    const starCount = Math.min(Math.max(parseInt(item.stars) || 5, 1), 5)
    let starStr = ''
    for (let i = 0; i < starCount; i++) starStr += '★ '
    for (let i = starCount; i < 5; i++) starStr += '☆ '
    starStr += ` (${starCount}/5)`

    doc.setFont('helvetica', 'bold')
    doc.setTextColor(217, 119, 6) // amber/gold
    doc.text(starStr, 125, y)

    doc.setDrawColor(241, 245, 249)
    doc.setLineWidth(0.2)
    doc.line(18, y + 2, 192, y + 2)

    y += 7.5
  })

  // Remarks Box
  y += 3
  doc.setFillColor(240, 253, 244)
  doc.setDrawColor(187, 247, 208)
  doc.rect(15, y, 180, 22, 'FD')

  doc.setFont('helvetica', 'bold')
  doc.setFontSize(8.5)
  doc.setTextColor(22, 101, 52)
  doc.text('REMARQUES ET APPRÉCIATIONS DE L\'ENSEIGNANT(E) :', 20, y + 6)

  doc.setFont('helvetica', 'normal')
  doc.setFontSize(8)
  doc.setTextColor(30, 41, 59)
  const splitRemarks = doc.splitTextToSize(remarks, 170)
  doc.text(splitRemarks, 20, y + 13)

  // Signature Block
  y += 28
  doc.setTextColor(100, 116, 139)
  doc.setFontSize(8)
  doc.text('Conseil Pédagogique & Direction', 25, y)
  doc.text('Cachet de l\'Établissement & Visa de l\'Enseignant', 125, y)

  doc.setFont('helvetica', 'bold')
  doc.text('École Tijanes Nours Luxembourg', 25, y + 6)
  doc.text('Cheikh Mahmoud', 125, y + 6)

  doc.save(`Bulletin_${studentName.replace(/\s+/g, '_')}_${term.replace(/\s+/g, '_')}.pdf`)
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

export const generateBilingualBulletinPdf = async (elementOrId, filename = 'Bulletin.pdf') => {
  const el = typeof elementOrId === 'string' ? document.getElementById(elementOrId) : elementOrId
  if (!el) throw new Error('Element introuvable pour la génération du bulletin')

  const canvas = await html2canvas(el, {
    scale: 2.5,
    useCORS: true,
    logging: false,
    backgroundColor: '#ffffff'
  })

  const imgData = canvas.toDataURL('image/jpeg', 0.98)
  const pdf = new jsPDF('p', 'mm', 'a4')
  const pdfWidth = pdf.internal.pageSize.getWidth()
  const pdfHeight = pdf.internal.pageSize.getHeight()

  pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight)
  pdf.save(filename)
  return pdf
}

export const getBilingualBulletinPdfBlob = async (elementOrId) => {
  const el = typeof elementOrId === 'string' ? document.getElementById(elementOrId) : elementOrId
  if (!el) throw new Error('Element introuvable pour la génération du bulletin')

  const canvas = await html2canvas(el, {
    scale: 2.2,
    useCORS: true,
    logging: false,
    backgroundColor: '#ffffff'
  })

  const imgData = canvas.toDataURL('image/jpeg', 0.95)
  const pdf = new jsPDF('p', 'mm', 'a4')
  const pdfWidth = pdf.internal.pageSize.getWidth()
  const pdfHeight = pdf.internal.pageSize.getHeight()

  pdf.addImage(imgData, 'JPEG', 0, 0, pdfWidth, pdfHeight)
  return pdf.output('blob')
}
