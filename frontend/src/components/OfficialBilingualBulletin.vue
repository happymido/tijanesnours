<template>
  <div
    :id="elementId || 'official-bilingual-bulletin'"
    class="bulletin-a4-page bg-white text-black p-8 mx-auto shadow-2xl print:shadow-none border border-gray-200 print:border-none"
    style="width: 210mm; min-height: 297mm; max-height: 297mm; box-sizing: border-box; font-family: 'Amiri', 'Noto Naskh Arabic', Arial, sans-serif; position: relative; padding: 10mm 15mm;"
  >
    <!-- Top Basmala -->
    <div class="text-center font-bold text-2xl mb-3 tracking-wide select-none font-arabic" style="direction: rtl;">
      بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
    </div>

    <!-- Header info Section: Left Admin Table & Right School Logo -->
    <div class="flex items-start justify-between gap-6 mb-3">
      <!-- Left Administrative Details Table -->
      <table class="admin-table border-collapse border border-black text-[11px] w-[58%] leading-tight">
        <tbody>
          <tr class="border-b border-black">
            <td class="border-r border-black p-1 text-center font-semibold bg-gray-50/50">
              <span class="font-bold">{{ data.student?.levelFr || 'Niveau 3' }}</span>
              <span class="mx-1 font-bold font-arabic">{{ data.student?.levelAr || 'المستوى 3' }}</span>
            </td>
            <td class="p-1 text-center font-bold bg-gray-100/70 w-32">
              Niveau المستوى
            </td>
          </tr>
          <tr class="border-b border-black">
            <td class="border-r border-black p-1 text-center font-semibold bg-gray-50/50">
              <span>{{ data.teacher?.fullName || 'Afifa Jlassi' }}</span>
              <span class="mx-1 font-arabic">{{ data.teacher?.fullNameAr || 'عفيفة الجلاصي' }}</span>
            </td>
            <td class="p-1 text-center font-bold bg-gray-100/70 w-32">
              Enseignant(e) المعلم(ة)
            </td>
          </tr>
          <tr class="border-b border-black">
            <td class="border-r border-black p-1 text-center font-semibold bg-gray-50/50">
              <span class="font-bold">{{ data.student?.firstName || 'Bayene' }}</span>
              <span class="mx-2 font-bold font-arabic">{{ data.student?.firstNameAr || 'بيان' }}</span>
            </td>
            <td class="p-1 text-center font-bold bg-gray-100/70 w-32">
              Prénom الاسم
            </td>
          </tr>
          <tr class="border-b border-black">
            <td class="border-r border-black p-1 text-center font-semibold bg-gray-50/50">
              <span class="font-bold">{{ data.student?.lastName || 'Harbi' }}</span>
              <span class="mx-2 font-bold font-arabic">{{ data.student?.lastNameAr || 'حربي' }}</span>
            </td>
            <td class="p-1 text-center font-bold bg-gray-100/70 w-32">
              Nom اللقب
            </td>
          </tr>
          <tr>
            <td class="border-r border-black p-1 text-center font-bold text-xs bg-gray-50/50">
              {{ data.absences?.count ?? 0 }}
            </td>
            <td class="p-1 text-center font-bold bg-gray-100/70 w-32">
              Absences الغيابات
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Right School Logo and Title -->
      <div class="flex flex-col items-center justify-center text-center w-[38%] pt-1">
        <img
          src="/logo.png"
          alt="Tijanes Nours"
          class="h-14 w-auto object-contain mb-1"
          @error="handleLogoError"
        />
        <h4 class="font-bold text-xs font-arabic tracking-wide leading-none text-gray-900">
          مدرسة تيجان النور
        </h4>
        <p class="text-[10px] font-bold text-gray-700 tracking-wider uppercase mt-0.5">
          ECOLE TIJANES NOURS
        </p>
      </div>
    </div>

    <!-- Period Title Banner -->
    <div class="text-center font-bold text-xs my-2.5 py-1 border-t border-b border-gray-500 bg-gray-50/40">
      <span>{{ data.period?.titleFr || 'Bilan du premier semestre 2024/2025' }}</span>
      <span class="mx-2">|</span>
      <span class="font-arabic">{{ data.period?.titleAr || 'سجل نتائج السداسي الأول 2024/2025' }}</span>
    </div>

    <!-- Main Competencies Table (3 Columns) -->
    <table class="border-collapse border border-black w-full text-[10.5px] mb-3 leading-normal">
      <thead>
        <tr class="bg-gray-100 border-b border-black text-center font-bold">
          <th class="border-r border-black py-1.5 px-2 w-[16%]">Notes العلامات</th>
          <th class="border-r border-black py-1.5 px-3 w-[60%]">Les compétences الكفايات</th>
          <th class="py-1.5 px-2 w-[24%]">Matières المواد</th>
        </tr>
      </thead>
      <tbody>
        <template v-for="subject in curriculumList" :key="subject.id">
          <tr
            v-for="(skill, index) in subject.skills"
            :key="skill.id"
            class="border-b border-black"
          >
            <!-- 1. Notes (Stars column: *, **, ***, ****) -->
            <td class="border-r border-black text-center font-extrabold text-xs tracking-widest align-middle py-1 px-1 bg-white">
              {{ getSkillStars(skill.id) }}
            </td>

            <!-- 2. Competence description (Bilingual French + Arabic) -->
            <td class="border-r border-black py-1 px-2.5 align-middle bg-white">
              <div class="flex items-center justify-between gap-2">
                <span class="text-left text-gray-900 leading-tight">
                  {{ skill.name }}
                </span>
                <span class="text-right font-arabic font-semibold text-gray-900 shrink-0 leading-tight" style="direction: rtl;">
                  {{ skill.nameAr || skill.name }}
                </span>
              </div>
            </td>

            <!-- 3. Subject column (rowspanned per subject) -->
            <td
              v-if="index === 0"
              :rowspan="subject.skills.length"
              class="text-center font-bold align-middle py-2 px-1 bg-gray-50/70 border-l border-black"
            >
              <div class="space-y-0.5">
                <div class="text-[11px] text-gray-900">{{ subject.name }}</div>
                <div class="text-[11px] font-arabic text-emerald-800">{{ subject.nameAr || subject.name }}</div>
              </div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>

    <!-- Teacher Remarks Box -->
    <div class="border border-black p-2 mb-3 bg-white">
      <div class="text-center font-bold text-xs border-b border-black pb-1 mb-2 font-arabic">
        ملاحظات المعلم(ة)
      </div>
      <!-- Arabic Remark -->
      <div class="text-right font-arabic text-[11px] leading-relaxed mb-2 font-medium px-1 text-gray-900" style="direction: rtl;">
        {{ data.teacherRemarks?.ar || 'تلميذ(ة) مجتهد(ة), خلوق(ة) وذو(ات) مهارات ممتازة وسلوك طيب. حفظك الله ووفقك دائما.' }}
      </div>
      <!-- French Remark -->
      <div class="text-left text-[10.5px] leading-relaxed text-gray-800 px-1">
        {{ data.teacherRemarks?.fr || 'Une bonne élève, polie, avec de bonnes compétences et un travail régulier.' }}
      </div>
    </div>

    <!-- Signatures Section -->
    <div class="flex justify-between gap-6 mb-3">
      <!-- Parent Signature Box -->
      <div class="border border-black w-1/2 p-2 h-16 flex flex-col justify-between bg-white text-center">
        <div class="font-bold text-xs font-arabic border-b border-gray-300 pb-1">
          إمضاء الولي
        </div>
        <div class="text-[9px] text-gray-400 italic">
          (Signature manuscrite du parent)
        </div>
      </div>

      <!-- Administration Signature Box -->
      <div class="border border-black w-1/2 p-2 h-16 flex flex-col justify-between bg-white text-center">
        <div class="font-bold text-xs font-arabic border-b border-gray-300 pb-1">
          الإدارة
        </div>
        <div class="text-[9px] text-gray-400 italic">
          (Cachet et signature de l'école)
        </div>
      </div>
    </div>

    <!-- Footer Evaluation Legend (4 Levels) -->
    <table class="border-collapse border border-black w-full text-[9.5px] leading-tight">
      <thead>
        <tr class="bg-gray-100 border-b border-black text-center font-bold">
          <th colspan="3" class="py-0.5 font-arabic text-[11px]">العلامات</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="item in legendItems"
          :key="item.symbol"
          class="border-b border-black last:border-b-0"
        >
          <!-- French text -->
          <td class="border-r border-black p-1 text-left w-1/2 pl-2 text-gray-800">
            {{ item.fr }}
          </td>
          <!-- Arabic text -->
          <td class="border-r border-black p-1 text-right w-[38%] pr-2 font-arabic font-medium text-gray-800" style="direction: rtl;">
            {{ item.ar }}
          </td>
          <!-- Symbol -->
          <td class="p-1 text-center font-black text-xs tracking-widest w-[12%] bg-gray-50/50">
            {{ item.symbol }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  elementId: {
    type: String,
    default: 'official-bilingual-bulletin'
  },
  data: {
    type: Object,
    required: true
  }
})

const curriculumList = computed(() => {
  if (Array.isArray(props.data?.curriculum) && props.data.curriculum.length > 0) {
    return props.data.curriculum
  }
  // Fallback official curriculum matching reference PDF
  return [
    {
      id: 1,
      name: 'Coran',
      nameAr: 'قرآن',
      skills: [
        { id: 1, name: 'Memorisation des sourates', nameAr: 'حفظ السور المقررة' }
      ]
    },
    {
      id: 2,
      name: 'Education Islamique',
      nameAr: 'تربية إسلامية',
      skills: [
        { id: 2, name: 'Acquisition des concepts religieux prescrits', nameAr: 'اكتساب المفاهيم الدينية المقررة' }
      ]
    },
    {
      id: 3,
      name: 'Lecture',
      nameAr: 'قراءة',
      skills: [
        { id: 3, name: 'Lecture fluide avec le respect de la ponctuation', nameAr: 'قراءة مسترسلة مع احترام الترقيم' },
        { id: 4, name: 'Compréhension de la lecture', nameAr: 'فهم المقروء' }
      ]
    },
    {
      id: 4,
      name: 'Expression Orale',
      nameAr: 'تعبير شفوي',
      skills: [
        { id: 5, name: 'Mémoriser les dialogues', nameAr: 'حفظ الحوارات' },
        { id: 6, name: 'Mémoriser le vocabulaire', nameAr: 'حفظ الزاد اللغوي' },
        { id: 7, name: 'Former des phrases avec les mots acquis', nameAr: 'تكوين جمل بالكلمات المكتسبة' },
        { id: 8, name: 'Compréhension orale', nameAr: 'فهم المسموع' }
      ]
    },
    {
      id: 5,
      name: 'Dictée',
      nameAr: 'إملاء و رسم',
      skills: [
        { id: 9, name: 'Écrire correctement les mots dictés en respectant les règles', nameAr: 'الكتابة الصحيحة لما يملى من كلمات مع احترام الحركات و المدود و الشدة' }
      ]
    }
  ]
})

const legendItems = computed(() => {
  return props.data?.legend || [
    { symbol: '*', fr: "Les objectifs n'ont pas encore été atteints", ar: 'لم يتم تحقيق أهداف التعلم بعد' },
    { symbol: '**', fr: 'Certains objectifs ont été atteints.', ar: 'تم تحقيق بعض الأهداف التعليمية' },
    { symbol: '***', fr: 'Objectifs atteints', ar: 'تم تحقيق جميع الأهداف التعليمية' },
    { symbol: '****', fr: 'Objectifs atteints avec excellence.', ar: 'تم تحقيق أهداف التعلم بامتياز' }
  ]
})

function getSkillStars(skillId) {
  if (props.data?.starSymbols && props.data.starSymbols[skillId]) {
    return props.data.starSymbols[skillId]
  }

  if (props.data?.ratings && props.data.ratings[skillId] !== undefined) {
    const r = parseInt(props.data.ratings[skillId], 10)
    if (r <= 1) return '*'
    if (r <= 3) return '**'
    if (r === 4) return '***'
    if (r >= 5) return '****'
  }

  return '***'
}

function handleLogoError(e) {
  e.target.style.display = 'none'
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400;1,700&family=Noto+Naskh+Arabic:wght@400;600;700&display=swap');

.bulletin-a4-page {
  font-family: 'Amiri', 'Noto Naskh Arabic', Arial, sans-serif;
  color: #000;
  line-height: 1.25;
}

.font-arabic {
  font-family: 'Amiri', 'Noto Naskh Arabic', serif;
}

@media print {
  @page {
    size: A4 portrait;
    margin: 8mm 12mm;
  }
  body * {
    visibility: hidden;
  }
  #official-bilingual-bulletin,
  #modal-official-bulletin,
  #modal-official-bulletin * {
    visibility: visible;
  }
  #modal-official-bulletin {
    position: absolute;
    left: 0;
    top: 0;
    width: 210mm !important;
    height: 297mm !important;
    margin: 0 !important;
    padding: 8mm 12mm !important;
    box-shadow: none !important;
    border: none !important;
  }
}
</style>
