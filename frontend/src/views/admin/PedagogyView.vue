<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion Pédagogique & Suivi Académique</h1>
        <p class="text-xs text-gray-500">Suivi des présences & absences, évaluations trimestrielles par compétences et gestion du référentiel</p>
      </div>
      <div class="flex flex-wrap gap-2.5">
        <button
          v-if="activeSubTab === 'ATTENDANCE'"
          @click="openAddAttendanceModal"
          class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1.5"
        >
          <span>+</span> Saisir / Modifier Émargement
        </button>

        <button
          v-if="activeSubTab === 'EVALUATIONS'"
          @click="openAddEvaluationModal"
          class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1.5"
        >
          <span>+</span> Saisir / Modifier Évaluation
        </button>

        <button
          v-if="activeSubTab === 'EVALUATIONS'"
          @click="generateAllBulletins"
          class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1.5"
        >
          <span>📄</span> Générer Bulletins PDF
        </button>

        <button
          v-if="activeSubTab === 'CURRICULUM'"
          @click="openAddSubjectModal"
          class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1.5"
        >
          <span>+</span> Ajouter une Matière
        </button>
      </div>
    </div>

    <!-- Tabs Filter -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6">
      <button
        @click="activeSubTab = 'ATTENDANCE'"
        :class="activeSubTab === 'ATTENDANCE' ? 'border-b-2 border-brand-600 text-brand-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'"
      >
        📋 Suivi des Présences & Absences ({{ attendanceList.length }})
      </button>

      <button
        @click="activeSubTab = 'EVALUATIONS'"
        :class="activeSubTab === 'EVALUATIONS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'"
      >
        ⭐ Évaluations Trimestrielles ({{ evaluationsList.length }})
      </button>

      <button
        @click="activeSubTab = 'CURRICULUM'"
        :class="activeSubTab === 'CURRICULUM' ? 'border-b-2 border-brand-600 text-brand-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'"
      >
        📚 Matières & Compétences ({{ curriculumList.length }})
      </button>
    </div>

    <div v-if="loading" class="p-8 text-center text-xs font-bold text-gray-500">
      Chargement des données pédagogiques...
    </div>

    <template v-else>
      <!-- 1. Suivi des Présences & Absences Section (Admin Full Access) -->
      <div v-if="activeSubTab === 'ATTENDANCE'" class="space-y-6">
        <!-- KPI Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Total Émargements</span>
            <p class="text-2xl font-black text-gray-900 dark:text-white">{{ attendanceStats.total }}</p>
            <span class="text-[10px] text-gray-400">Séances saisies en BBD</span>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block mb-1">Taux d'Assiduité</span>
            <p class="text-2xl font-black text-emerald-600">{{ attendanceStats.rate }}%</p>
            <span class="text-[10px] text-emerald-600 font-semibold">{{ attendanceStats.presents }} Présence(s) validée(s)</span>
          </div>

          <div
            @click="attendanceStatusFilter = 'ABSENT'"
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-red-200 dark:border-red-900/50 cursor-pointer hover:bg-red-50/30 transition-all"
            title="Cliquer pour filtrer les absents"
          >
            <span class="text-[11px] font-bold text-red-600 uppercase tracking-wider block mb-1">Total Absences</span>
            <p class="text-2xl font-black text-red-600">{{ attendanceStats.absents }}</p>
            <span class="text-[10px] text-red-500 font-semibold">Comptabilisées en base</span>
          </div>

          <div
            @click="attendanceStatusFilter = 'LATE'"
            class="bg-white dark:bg-gray-800 rounded-2xl p-4 shadow-sm border border-amber-200 dark:border-amber-900/50 cursor-pointer hover:bg-amber-50/30 transition-all"
            title="Cliquer pour filtrer les retards"
          >
            <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block mb-1">Total Retards</span>
            <p class="text-2xl font-black text-amber-600">{{ attendanceStats.lates }}</p>
            <span class="text-[10px] text-amber-500 font-semibold">Ponctualité à surveiller</span>
          </div>
        </div>

        <!-- Filter Toolbar -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b pb-4 dark:border-gray-700">
            <div>
              <h3 class="font-bold text-base text-gray-900 dark:text-white">Registre Général des Émargements</h3>
              <p class="text-xs text-gray-500">Supervision en direct de l'appel saisi par les enseignants ou régularisé par l'administration</p>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-2">
              <input
                v-model="attendanceSearchQuery"
                type="text"
                placeholder="Rechercher élève..."
                class="px-3 py-1.5 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs text-gray-800 dark:text-gray-200"
              />

              <select v-model="attendanceClassFilter" class="px-3 py-1.5 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-semibold">
                <option value="ALL">Toutes les classes</option>
                <option v-for="c in classFilterOptions" :key="c" :value="c">{{ c }}</option>
              </select>

              <select v-model="attendanceStatusFilter" class="px-3 py-1.5 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-semibold">
                <option value="ALL">Tous les statuts</option>
                <option value="PRESENT">🟢 Présents uniquement</option>
                <option value="ABSENT">🔴 Absents uniquement</option>
                <option value="LATE">🟡 Retards uniquement</option>
              </select>

              <button
                @click="openAddAttendanceModal"
                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1"
              >
                <span>+</span> Ajouter / Modifier
              </button>
            </div>
          </div>

          <!-- Attendance Table -->
          <div v-if="filteredAttendances.length === 0" class="py-12 text-center text-xs font-bold text-gray-400">
            Aucun enregistrement d'émargement ne correspond aux filtres sélectionnés.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
                <tr>
                  <th class="py-3 px-4">Élève</th>
                  <th class="py-3 px-4">Classe</th>
                  <th class="py-3 px-4">Date Séance</th>
                  <th class="py-3 px-4">Statut</th>
                  <th class="py-3 px-4">Motif / Justification</th>
                  <th class="py-3 px-4 text-right">Actions Admin</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="att in filteredAttendances" :key="att.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                  <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">
                    {{ att.student }}
                  </td>
                  <td class="py-3.5 px-4 font-semibold text-gray-600 dark:text-gray-300">
                    {{ att.classroom }}
                  </td>
                  <td class="py-3.5 px-4 text-gray-500 font-medium">
                    {{ att.date }}
                  </td>
                  <td class="py-3.5 px-4">
                    <span
                      class="px-2.5 py-1 rounded-full text-[10px] font-bold"
                      :class="{
                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300': att.status === 'PRÉSENT',
                        'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300': att.status === 'ABSENT',
                        'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300': att.status === 'RETARD'
                      }"
                    >
                      {{ att.status === 'PRÉSENT' ? '🟢 Présent' : (att.status === 'ABSENT' ? '🔴 Absent' : '🟡 Retard') }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-gray-500">
                    {{ att.justification || '—' }}
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <button
                        @click="editAttendance(att)"
                        class="p-1.5 rounded-lg border hover:bg-gray-100 text-gray-600 font-bold text-[11px]"
                        title="Modifier le statut ou la justification"
                      >
                        ✏️
                      </button>
                      <button
                        @click="deleteAttendanceRecord(att.id)"
                        class="p-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 font-bold text-[11px]"
                        title="Supprimer cet enregistrement"
                      >
                        🗑️
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 2. Relevé des Évaluations Trimestrielles (Étoiles 1 à 5 ⭐) -->
      <div v-if="activeSubTab === 'EVALUATIONS'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-gray-700">
            <div>
              <h3 class="font-bold text-base text-gray-900 dark:text-white flex items-center gap-2">
                <span>⭐</span> Relevé des Évaluations Trimestrielles par Compétences
              </h3>
              <p class="text-xs text-gray-500">
                Notation de 1 à 5 étoiles (⭐), appréciations et génération des bulletins scolaires officiels PDF.
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
              <select v-model="selectedClassFilter" class="px-3 py-1.5 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-semibold">
                <option value="ALL">Toutes les classes</option>
                <option v-for="c in classFilterOptions" :key="c" :value="c">{{ c }}</option>
              </select>

              <select v-model="selectedEvaluationTermFilter" class="px-3 py-1.5 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-semibold">
                <option value="ALL">Tous les trimestres</option>
                <option value="Trimestre 1">Trimestre 1</option>
                <option value="Trimestre 2">Trimestre 2</option>
                <option value="Trimestre 3">Trimestre 3</option>
              </select>
            </div>
          </div>

          <div v-if="filteredEvaluations.length === 0" class="py-12 text-center text-xs font-bold text-gray-400">
            Aucune évaluation enregistrée pour ce filtre.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
                <tr>
                  <th class="py-3 px-4">Élève</th>
                  <th class="py-3 px-4">Classe</th>
                  <th class="py-3 px-4">Trimestre</th>
                  <th class="py-3 px-4">Moyenne (Étoiles)</th>
                  <th class="py-3 px-4">Remarques de l'Enseignant(e)</th>
                  <th class="py-3 px-4">Mise à jour</th>
                  <th class="py-3 px-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="ev in filteredEvaluations" :key="ev.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                  <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">
                    {{ ev.student }}
                  </td>
                  <td class="py-3.5 px-4 font-semibold text-gray-600 dark:text-gray-300">
                    {{ ev.classroom }}
                  </td>
                  <td class="py-3.5 px-4">
                    <span class="px-2.5 py-0.5 rounded-full bg-brand-50 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 font-bold text-[10px]">
                      {{ ev.term }}
                    </span>
                  </td>
                  <td class="py-3.5 px-4">
                    <span class="px-2.5 py-1 rounded-xl bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300 font-black text-xs border border-amber-200">
                      ⭐ {{ ev.averageStars }} / 5
                    </span>
                  </td>
                  <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300 italic max-w-xs truncate">
                    "{{ ev.teacherRemarks }}"
                  </td>
                  <td class="py-3.5 px-4 text-gray-400 font-mono text-[11px]">
                    {{ ev.updatedAt }}
                  </td>
                  <td class="py-3.5 px-4 text-right">
                    <div class="flex items-center justify-end gap-1.5">
                      <button
                        @click="editEvaluation(ev)"
                        class="p-1.5 rounded-lg border hover:bg-gray-100 text-brand-600 font-bold text-xs"
                        title="Modifier l'évaluation et les étoiles"
                      >
                        ✏️
                      </button>
                      <button
                        @click="downloadEvaluationPdf(ev)"
                        class="p-1.5 rounded-lg border border-indigo-200 text-indigo-600 hover:bg-indigo-50 font-bold text-xs"
                        title="Télécharger Bulletin PDF officiel"
                      >
                        📄
                      </button>
                      <button
                        @click="deleteEvaluationRecord(ev.id)"
                        class="p-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 font-bold text-xs"
                        title="Supprimer cette évaluation"
                      >
                        🗑️
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 3. Gestion Dédiée des Matières & Compétences (Interface BBD) -->
      <div v-if="activeSubTab === 'CURRICULUM'" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-gray-700">
            <div>
              <h3 class="font-bold text-base text-gray-900 dark:text-white flex items-center gap-2">
                <span>📚</span> Référentiel des Matières & Compétences Pédagogiques
              </h3>
              <p class="text-xs text-gray-500">
                Ajoutez, modifiez ou supprimez les matières et les compétences évaluées. Tout est persisté dans MySQL.
              </p>
            </div>
            <button
              @click="openAddSubjectModal"
              class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1.5"
            >
              <span>+</span> Ajouter une Matière
            </button>
          </div>

          <div v-if="curriculumList.length === 0" class="py-12 text-center text-xs font-bold text-gray-400">
            Aucune matière configurée en base de données.
          </div>

          <!-- Grid of Subjects -->
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              v-for="subject in curriculumList"
              :key="subject.id"
              class="bg-gray-50/70 dark:bg-gray-700/40 rounded-2xl p-5 border border-gray-200 dark:border-gray-700 space-y-4 flex flex-col justify-between"
            >
              <div class="space-y-3">
                <!-- Subject Header -->
                <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
                  <div class="flex items-center gap-2.5">
                    <span class="text-2xl p-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm">{{ subject.icon || '📚' }}</span>
                    <div>
                      <h4 class="font-bold text-sm text-gray-900 dark:text-white">{{ subject.name }}</h4>
                      <span class="text-[10px] text-gray-400 font-mono">Code : {{ subject.code }} • Ordre : {{ subject.orderIndex }}</span>
                    </div>
                  </div>

                  <div class="flex items-center gap-1.5">
                    <button
                      @click="editSubject(subject)"
                      class="p-1.5 rounded-lg border bg-white dark:bg-gray-800 text-gray-600 hover:text-brand-600 text-xs font-bold shadow-sm"
                      title="Modifier la matière"
                    >
                      ✏️
                    </button>
                    <button
                      @click="deleteSubjectRecord(subject.id)"
                      class="p-1.5 rounded-lg border border-red-200 bg-white dark:bg-gray-800 text-red-600 hover:bg-red-50 text-xs font-bold shadow-sm"
                      title="Supprimer la matière"
                    >
                      🗑️
                    </button>
                  </div>
                </div>

                <!-- Skills List inside this subject -->
                <div class="space-y-2">
                  <div class="flex items-center justify-between text-[11px] font-bold text-gray-500">
                    <span>Compétences associées ({{ (subject.skills || []).length }}) :</span>
                  </div>

                  <div v-if="!subject.skills || subject.skills.length === 0" class="text-[11px] text-gray-400 italic py-2">
                    Aucune compétence configurée pour cette matière.
                  </div>

                  <div
                    v-for="skill in subject.skills"
                    :key="skill.id"
                    class="p-2.5 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs gap-2"
                  >
                    <div class="flex items-center gap-2">
                      <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                      <span class="font-medium text-gray-800 dark:text-gray-200 text-[11px]">{{ skill.name }}</span>
                    </div>

                    <div class="flex items-center gap-1 shrink-0">
                      <button
                        @click="editSkill(skill, subject.id)"
                        class="p-1 text-gray-400 hover:text-brand-600 text-[11px]"
                        title="Modifier compétence"
                      >
                        ✏️
                      </button>
                      <button
                        @click="deleteSkillRecord(skill.id)"
                        class="p-1 text-gray-400 hover:text-red-600 text-[11px]"
                        title="Supprimer compétence"
                      >
                        ✕
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Add Skill Button for this subject -->
              <div class="pt-3 border-t dark:border-gray-700">
                <button
                  @click="openAddSkillModal(subject.id)"
                  class="w-full py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300 font-bold text-xs rounded-xl transition-all flex items-center justify-center gap-1.5"
                >
                  <span>+</span> Ajouter une compétence à {{ subject.name }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Modal Admin Saisie / Modification Émargement -->
    <div v-if="showAttendanceModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-5 text-xs border dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
          {{ attendanceModalForm.id ? 'Modifier un Émargement' : 'Enregistrer une Présence / Absence' }}
        </h3>
        <p class="text-xs text-gray-500">
          Ce changement mettra directement à jour la base de données et l'espace de l'élève.
        </p>

        <div>
          <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Sélectionner l'Élève *</label>
          <select
            v-model="attendanceModalForm.studentId"
            required
            :disabled="!!attendanceModalForm.id"
            class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold"
          >
            <option value="">Choisir un élève...</option>
            <option v-for="st in studentsOptions" :key="st.id" :value="st.dbId || st.id">
              {{ st.name }} ({{ st.assignedGroup }})
            </option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Date de la séance *</label>
            <input
              v-model="attendanceModalForm.date"
              type="date"
              required
              class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold"
            />
          </div>
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Statut *</label>
            <select v-model="attendanceModalForm.status" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold">
              <option value="PRESENT">🟢 Présent</option>
              <option value="ABSENT">🔴 Absent</option>
              <option value="LATE">🟡 Retard</option>
            </select>
          </div>
        </div>

        <div>
          <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Motif / Justification</label>
          <input
            v-model="attendanceModalForm.justification"
            type="text"
            placeholder="ex: Certificat médical, motif familial..."
            class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700"
          />
        </div>

        <div class="flex gap-3 pt-2">
          <button
            @click="saveAdminAttendance"
            :disabled="savingAttendance"
            class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow transition-all disabled:opacity-50"
          >
            {{ savingAttendance ? 'Enregistrement...' : 'Enregistrer en Base' }}
          </button>
          <button type="button" @click="showAttendanceModal = false" class="py-3 px-4 border rounded-xl font-semibold text-gray-600 dark:text-gray-300">
            Annuler
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Admin Saisie / Modification Évaluation Trimestrielle (Étoiles 1 à 5 ⭐) -->
    <div v-if="showEvaluationModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-2xl w-full shadow-2xl space-y-5 text-xs border dark:border-gray-700 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <span>⭐</span> {{ evaluationModalForm.id ? 'Modifier l\'Évaluation' : 'Saisir une Évaluation par Compétences' }}
          </h3>
          <button @click="showEvaluationModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Élève *</label>
            <select
              v-model="evaluationModalForm.studentId"
              required
              class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold"
            >
              <option value="">Sélectionner un élève...</option>
              <option v-for="st in studentsOptions" :key="st.id" :value="st.dbId || st.id">
                {{ st.name }} ({{ st.assignedGroup }})
              </option>
            </select>
          </div>

          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Trimestre *</label>
            <select v-model="evaluationModalForm.term" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold">
              <option value="Trimestre 1">Trimestre 1</option>
              <option value="Trimestre 2">Trimestre 2</option>
              <option value="Trimestre 3">Trimestre 3</option>
            </select>
          </div>
        </div>

        <!-- Competencies Evaluation Grid in Modal -->
        <div class="space-y-4">
          <h4 class="font-bold text-xs text-gray-800 dark:text-gray-200 border-b pb-1">Notation des Compétences (1 à 5 Étoiles) :</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-72 overflow-y-auto p-1">
            <div
              v-for="subject in curriculumList"
              :key="subject.id"
              class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600 space-y-2"
            >
              <span class="font-bold text-xs text-emerald-800 dark:text-emerald-300 block">
                {{ subject.icon }} {{ subject.name }}
              </span>

              <div v-for="skill in subject.skills" :key="skill.id" class="text-[11px] space-y-1">
                <span class="text-gray-700 dark:text-gray-300 block font-medium">{{ skill.name }}</span>
                <div class="flex items-center gap-1">
                  <button
                    v-for="star in 5"
                    :key="star"
                    type="button"
                    @click="evaluationModalForm.ratings[skill.id] = star"
                    class="text-base font-bold transition-all focus:outline-none"
                    :class="star <= (evaluationModalForm.ratings[skill.id] || 5) ? 'text-amber-400 hover:scale-125' : 'text-gray-300 dark:text-gray-600'"
                  >
                    ★
                  </button>
                  <span class="text-[10px] text-gray-400 font-mono ml-1 font-bold">
                    {{ evaluationModalForm.ratings[skill.id] || 5 }}/5
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Remarks -->
        <div>
          <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-300">Remarques et appréciations de l'enseignant(e)</label>
          <textarea
            v-model="evaluationModalForm.teacherRemarks"
            rows="3"
            placeholder="Appréciation globale, assiduité, comportement et axes de progrès..."
            class="w-full px-3.5 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700"
          ></textarea>
        </div>

        <div class="flex gap-3 pt-2">
          <button
            @click="saveAdminEvaluation"
            :disabled="savingEvaluation"
            class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow transition-all disabled:opacity-50"
          >
            {{ savingEvaluation ? 'Enregistrement...' : 'Enregistrer Évaluation en Base' }}
          </button>
          <button type="button" @click="showEvaluationModal = false" class="py-3 px-4 border rounded-xl font-semibold text-gray-600 dark:text-gray-300">
            Annuler
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Ajouter / Modifier Matière (Subject) -->
    <div v-if="showSubjectModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-4 text-xs border dark:border-gray-700">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">
            {{ subjectModalForm.id ? '✏️ Modifier la Matière' : '📚 Nouvelle Matière Pédagogique' }}
          </h3>
          <button @click="showSubjectModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <div>
          <label class="block font-semibold mb-1">Nom de la Matière *</label>
          <input v-model="subjectModalForm.name" type="text" required placeholder="ex: Saint Coran" class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold mb-1">Code Unique</label>
            <input v-model="subjectModalForm.code" type="text" placeholder="ex: CORAN" class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-mono" />
          </div>
          <div>
            <label class="block font-semibold mb-1">Icône Emoji</label>
            <input v-model="subjectModalForm.icon" type="text" placeholder="ex: 📖" class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 text-center font-bold text-lg" />
          </div>
        </div>

        <div>
          <label class="block font-semibold mb-1">Ordre d'Affichage</label>
          <input v-model.number="subjectModalForm.orderIndex" type="number" min="0" class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700" />
        </div>

        <div class="flex gap-3 pt-3">
          <button @click="saveSubject" class="flex-1 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow">
            💾 Enregistrer Matière
          </button>
          <button type="button" @click="showSubjectModal = false" class="py-3 px-4 border rounded-xl text-gray-600 font-semibold">
            Annuler
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Ajouter / Modifier Compétence (Skill) -->
    <div v-if="showSkillModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-4 text-xs border dark:border-gray-700">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <h3 class="text-lg font-bold text-gray-900 dark:text-white">
            {{ skillModalForm.id ? '✏️ Modifier la Compétence' : '🎯 Nouvelle Compétence' }}
          </h3>
          <button @click="showSkillModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <div>
          <label class="block font-semibold mb-1">Matière Parente *</label>
          <select v-model="skillModalForm.subjectId" required class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold">
            <option v-for="s in curriculumList" :key="s.id" :value="s.id">{{ s.icon }} {{ s.name }}</option>
          </select>
        </div>

        <div>
          <label class="block font-semibold mb-1">Intitulé de la Compétence *</label>
          <input v-model="skillModalForm.name" type="text" required placeholder="ex: Mémorisation des sourates" class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold" />
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block font-semibold mb-1">Code Technique</label>
            <input v-model="skillModalForm.code" type="text" placeholder="ex: memorisation_sourates" class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-mono text-[11px]" />
          </div>
          <div>
            <label class="block font-semibold mb-1">Ordre</label>
            <input v-model.number="skillModalForm.orderIndex" type="number" min="0" class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700" />
          </div>
        </div>

        <div class="flex gap-3 pt-3">
          <button @click="saveSkill" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow">
            💾 Enregistrer Compétence
          </button>
          <button type="button" @click="showSkillModal = false" class="py-3 px-4 border rounded-xl text-gray-600 font-semibold">
            Annuler
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import apiClient from '../../plugins/axios'
import { generateBulletinPdf } from '../../plugins/pdfGenerator'
import { showSuccessAlert, showErrorAlert } from '../../plugins/notify'

const activeSubTab = ref('ATTENDANCE')
const selectedClassFilter = ref('ALL')
const selectedEvaluationTermFilter = ref('ALL')

const loading = ref(false)
const savingAttendance = ref(false)
const savingEvaluation = ref(false)

const studentsOptions = ref([])
const configuredClasses = ref([])
const attendanceList = ref([])
const evaluationsList = ref([])
const curriculumList = ref([])

const showAttendanceModal = ref(false)
const showEvaluationModal = ref(false)
const showSubjectModal = ref(false)
const showSkillModal = ref(false)

const attendanceStats = ref({
  total: 0,
  presents: 0,
  absents: 0,
  lates: 0,
  rate: 100.0
})

const attendanceSearchQuery = ref('')
const attendanceClassFilter = ref('ALL')
const attendanceStatusFilter = ref('ALL')

const attendanceModalForm = ref({
  id: null,
  studentId: '',
  date: new Date().toISOString().split('T')[0],
  status: 'PRESENT',
  justification: ''
})

const evaluationModalForm = ref({
  id: null,
  studentId: '',
  term: 'Trimestre 1',
  ratings: {},
  teacherRemarks: ''
})

const subjectModalForm = ref({
  id: null,
  name: '',
  code: '',
  icon: '📚',
  orderIndex: 0,
  isActive: true
})

const skillModalForm = ref({
  id: null,
  subjectId: null,
  name: '',
  code: '',
  orderIndex: 0,
  isActive: true
})

const classFilterOptions = computed(() => {
  const set = new Set()
  if (Array.isArray(configuredClasses.value)) {
    configuredClasses.value.forEach(c => {
      if (c && c.name) set.add(c.name)
    })
  }
  studentsOptions.value.forEach(s => {
    if (s.assignedGroup) {
      s.assignedGroup.split(',').forEach(p => {
        const trimmed = p.trim()
        if (trimmed) set.add(trimmed)
      })
    }
  })
  attendanceList.value.forEach(a => {
    if (a.classroom) set.add(a.classroom)
  })
  return Array.from(set).sort()
})

const filteredAttendances = computed(() => {
  return attendanceList.value.filter(att => {
    if (attendanceSearchQuery.value) {
      const q = attendanceSearchQuery.value.toLowerCase()
      if (!att.student.toLowerCase().includes(q)) return false
    }

    if (attendanceClassFilter.value !== 'ALL') {
      if (!att.classroom.toLowerCase().includes(attendanceClassFilter.value.toLowerCase())) {
        return false
      }
    }

    if (attendanceStatusFilter.value !== 'ALL') {
      if (attendanceStatusFilter.value === 'PRESENT' && att.status !== 'PRÉSENT') return false
      if (attendanceStatusFilter.value === 'ABSENT' && att.status !== 'ABSENT') return false
      if (attendanceStatusFilter.value === 'LATE' && att.status !== 'RETARD') return false
    }

    return true
  })
})

const filteredEvaluations = computed(() => {
  return evaluationsList.value.filter(ev => {
    if (selectedClassFilter.value !== 'ALL') {
      if (!ev.classroom.toLowerCase().includes(selectedClassFilter.value.toLowerCase())) {
        return false
      }
    }
    if (selectedEvaluationTermFilter.value !== 'ALL') {
      if (ev.term !== selectedEvaluationTermFilter.value) {
        return false
      }
    }
    return true
  })
})

async function fetchPedagogyData() {
  loading.value = true
  try {
    const [studentsRes, classesRes, attRes, evRes, curRes] = await Promise.all([
      apiClient.get('/admin/students').catch(() => ({ data: [] })),
      apiClient.get('/admin/classes').catch(() => ({ data: { classes: [] } })),
      apiClient.get('/admin/pedagogy/attendance').catch(() => ({ data: {} })),
      apiClient.get('/admin/pedagogy/evaluations').catch(() => ({ data: [] })),
      apiClient.get('/admin/pedagogy/curriculum').catch(() => ({ data: [] }))
    ])

    if (classesRes.data && Array.isArray(classesRes.data.classes)) {
      configuredClasses.value = classesRes.data.classes
    }

    if (Array.isArray(studentsRes.data)) {
      studentsOptions.value = studentsRes.data.filter(u => u.role === 'ROLE_STUDENT' || !u.role)
    }

    if (Array.isArray(evRes.data)) {
      evaluationsList.value = evRes.data
    }

    if (Array.isArray(curRes.data)) {
      curriculumList.value = curRes.data
    }

    if (attRes.data) {
      if (Array.isArray(attRes.data.items)) {
        attendanceList.value = attRes.data.items
      } else if (Array.isArray(attRes.data)) {
        attendanceList.value = attRes.data
      }

      if (attRes.data.stats) {
        attendanceStats.value = attRes.data.stats
      } else {
        const total = attendanceList.value.length
        const absents = attendanceList.value.filter(a => a.status === 'ABSENT').length
        const lates = attendanceList.value.filter(a => a.status === 'RETARD').length
        const presents = total - absents - lates
        attendanceStats.value = {
          total,
          presents,
          absents,
          lates,
          rate: total > 0 ? Math.round((presents / total) * 100) : 100.0
        }
      }
    }
  } catch (err) {
    console.error('Erreur chargement données pédagogiques:', err)
  } finally {
    loading.value = false
  }
}

// Attendance methods
function openAddAttendanceModal() {
  attendanceModalForm.value = {
    id: null,
    studentId: studentsOptions.value[0]?.dbId || studentsOptions.value[0]?.id || '',
    date: new Date().toISOString().split('T')[0],
    status: 'PRESENT',
    justification: ''
  }
  showAttendanceModal.value = true
}

function editAttendance(att) {
  attendanceModalForm.value = {
    id: att.id,
    studentId: att.studentId,
    date: att.dateRaw || new Date().toISOString().split('T')[0],
    status: att.statusCode || (att.status === 'PRÉSENT' ? 'PRESENT' : (att.status === 'ABSENT' ? 'ABSENT' : 'LATE')),
    justification: att.justification || ''
  }
  showAttendanceModal.value = true
}

async function saveAdminAttendance() {
  if (!attendanceModalForm.value.studentId) return
  savingAttendance.value = true

  try {
    const payload = {
      studentId: attendanceModalForm.value.studentId,
      date: attendanceModalForm.value.date,
      status: attendanceModalForm.value.status,
      justification: attendanceModalForm.value.justification
    }

    const res = await apiClient.post('/admin/pedagogy/attendance', payload)
    showAttendanceModal.value = false
    showSuccessAlert('Émargement Enregistré ! 🎉', res.data.message || 'L\'émargement a été mis à jour en base de données.')
    await fetchPedagogyData()
  } catch (err) {
    console.error('Erreur enregistrement émargement:', err)
    showErrorAlert('Erreur', 'Erreur lors de l\'enregistrement de l\'émargement en base de données.')
  } finally {
    savingAttendance.value = false
  }
}

async function deleteAttendanceRecord(id) {
  if (!confirm('Voulez-vous vraiment supprimer cet émargement de la base de données ?')) {
    return
  }

  try {
    await apiClient.delete(`/admin/pedagogy/attendance/${id}`)
    showSuccessAlert('Supprimé', 'L\'émargement a été supprimé de la base de données.')
    await fetchPedagogyData()
  } catch (err) {
    console.error('Erreur suppression émargement:', err)
    showErrorAlert('Erreur', 'Impossible de supprimer cet enregistrement.')
  }
}

// Evaluation Methods
function openAddEvaluationModal() {
  const defaultRatings = {}
  curriculumList.value.forEach(s => {
    s.skills?.forEach(sk => {
      defaultRatings[sk.id] = 5
    })
  })

  evaluationModalForm.value = {
    id: null,
    studentId: studentsOptions.value[0]?.dbId || studentsOptions.value[0]?.id || '',
    term: 'Trimestre 1',
    ratings: defaultRatings,
    teacherRemarks: 'Très bon investissement et régularité exemplaire.'
  }
  showEvaluationModal.value = true
}

function editEvaluation(ev) {
  const defaultRatings = {}
  curriculumList.value.forEach(s => {
    s.skills?.forEach(sk => {
      defaultRatings[sk.id] = 5
    })
  })

  evaluationModalForm.value = {
    id: ev.id,
    studentId: ev.studentId,
    term: ev.term || 'Trimestre 1',
    ratings: ev.ratings && Object.keys(ev.ratings).length > 0 ? { ...ev.ratings } : defaultRatings,
    teacherRemarks: ev.teacherRemarks || ''
  }
  showEvaluationModal.value = true
}

async function saveAdminEvaluation() {
  if (!evaluationModalForm.value.studentId) return
  savingEvaluation.value = true

  try {
    const payload = {
      studentId: evaluationModalForm.value.studentId,
      term: evaluationModalForm.value.term,
      schoolYear: '2026-2027',
      ratings: evaluationModalForm.value.ratings,
      teacherRemarks: evaluationModalForm.value.teacherRemarks
    }

    const res = await apiClient.post('/admin/pedagogy/evaluations', payload)
    showEvaluationModal.value = false
    showSuccessAlert('Évaluation Enregistrée ! 🌟', res.data.message || 'L\'évaluation a été mise à jour en base de données.')
    await fetchPedagogyData()
  } catch (err) {
    console.error('Erreur enregistrement évaluation:', err)
    showErrorAlert('Erreur', err.response?.data?.error || 'Erreur lors de l\'enregistrement de l\'évaluation.')
  } finally {
    savingEvaluation.value = false
  }
}

async function deleteEvaluationRecord(id) {
  if (!confirm('Voulez-vous vraiment supprimer cette évaluation trimestrielle ?')) {
    return
  }

  try {
    await apiClient.delete(`/admin/pedagogy/evaluations/${id}`)
    showSuccessAlert('Supprimée', 'L\'évaluation a été supprimée de la base de données.')
    await fetchPedagogyData()
  } catch (err) {
    console.error('Erreur suppression évaluation:', err)
    showErrorAlert('Erreur', 'Impossible de supprimer cette évaluation.')
  }
}

function downloadEvaluationPdf(ev) {
  generateBulletinPdf(ev.student, ev.classroom, {
    ...ev,
    curriculum: curriculumList.value
  })
  showSuccessAlert('Téléchargement PDF', `Le bulletin scolaire de ${ev.student} (${ev.term}) a été généré et téléchargé.`)
}

function generateAllBulletins() {
  if (evaluationsList.value.length === 0) {
    generateBulletinPdf('Youssef Benali', 'Classe Débutant 2A (6-8 ans)', { curriculum: curriculumList.value })
  } else {
    evaluationsList.value.forEach(ev => {
      generateBulletinPdf(ev.student, ev.classroom, {
        ...ev,
        curriculum: curriculumList.value
      })
    })
  }
  showSuccessAlert('Bulletins Générés ! 📄', `Les bulletins trimestriels officiels par compétences ont été générés.`)
}

// Curriculum (Subjects & Skills) Methods
function openAddSubjectModal() {
  subjectModalForm.value = {
    id: null,
    name: '',
    code: '',
    icon: '📚',
    orderIndex: curriculumList.value.length + 1,
    isActive: true
  }
  showSubjectModal.value = true
}

function editSubject(subject) {
  subjectModalForm.value = {
    id: subject.id,
    name: subject.name,
    code: subject.code,
    icon: subject.icon,
    orderIndex: subject.orderIndex,
    isActive: subject.isActive
  }
  showSubjectModal.value = true
}

async function saveSubject() {
  if (!subjectModalForm.value.name) return

  try {
    await apiClient.post('/admin/pedagogy/subjects', subjectModalForm.value)
    showSubjectModal.value = false
    showSuccessAlert('Matière Enregistrée ! 📚', 'La matière a été enregistrée avec succès en base de données.')
    await fetchPedagogyData()
  } catch (err) {
    console.error('Erreur enregistrement matière:', err)
    showErrorAlert('Erreur', err.response?.data?.error || 'Erreur lors de l\'enregistrement de la matière.')
  }
}

async function deleteSubjectRecord(id) {
  if (!confirm('Attention : supprimer cette matière supprimera également toutes ses compétences associées. Confirmer ?')) {
    return
  }

  try {
    await apiClient.delete(`/admin/pedagogy/subjects/${id}`)
    showSuccessAlert('Matière Supprimée', 'La matière et ses compétences ont été supprimées.')
    await fetchPedagogyData()
  } catch (err) {
    console.error('Erreur suppression matière:', err)
    showErrorAlert('Erreur', 'Impossible de supprimer cette matière.')
  }
}

function openAddSkillModal(subjectId = null) {
  const targetSubId = subjectId || (curriculumList.value[0]?.id || null)
  const targetSub = curriculumList.value.find(s => s.id === targetSubId)
  skillModalForm.value = {
    id: null,
    subjectId: targetSubId,
    name: '',
    code: '',
    orderIndex: (targetSub?.skills?.length || 0) + 1,
    isActive: true
  }
  showSkillModal.value = true
}

function editSkill(skill, subjectId) {
  skillModalForm.value = {
    id: skill.id,
    subjectId: subjectId,
    name: skill.name,
    code: skill.code,
    orderIndex: skill.orderIndex,
    isActive: skill.isActive
  }
  showSkillModal.value = true
}

async function saveSkill() {
  if (!skillModalForm.value.name || !skillModalForm.value.subjectId) return

  try {
    await apiClient.post('/admin/pedagogy/skills', skillModalForm.value)
    showSkillModal.value = false
    showSuccessAlert('Compétence Enregistrée ! 🎯', 'La compétence a été enregistrée avec succès en base de données.')
    await fetchPedagogyData()
  } catch (err) {
    console.error('Erreur enregistrement compétence:', err)
    showErrorAlert('Erreur', err.response?.data?.error || 'Erreur lors de l\'enregistrement de la compétence.')
  }
}

async function deleteSkillRecord(id) {
  if (!confirm('Voulez-vous vraiment supprimer cette compétence ?')) {
    return
  }

  try {
    await apiClient.delete(`/admin/pedagogy/skills/${id}`)
    showSuccessAlert('Compétence Supprimée', 'La compétence a été supprimée.')
    await fetchPedagogyData()
  } catch (err) {
    console.error('Erreur suppression compétence:', err)
    showErrorAlert('Erreur', 'Impossible de supprimer cette compétence.')
  }
}

onMounted(() => {
  fetchPedagogyData()
})
</script>
