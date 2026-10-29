<template>
  <div class="space-y-8">
    <!-- Header Banner Enseignant -->
    <div class="bg-gradient-to-r from-emerald-800 via-emerald-700 to-teal-600 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div>
        <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider text-emerald-200">
          Portail Enseignant & Pédagogie
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold mt-2">Bienvenue, {{ teacherName }}</h1>
        <p class="text-emerald-100 text-sm mt-1">Feuille d'appel numérique, comptabilisation des absences et carnet d'évaluations par compétences</p>
      </div>

      <div class="flex items-center gap-3">
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20">
          <span class="text-[10px] uppercase font-bold text-emerald-200 block">Classes Affectées</span>
          <strong class="text-xl font-extrabold">{{ assignedClasses.length }}</strong>
        </div>
        <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl text-center border border-white/20">
          <span class="text-[10px] uppercase font-bold text-emerald-200 block">Élèves En Classe</span>
          <strong class="text-xl font-extrabold">{{ studentsList.length }}</strong>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs Enseignant -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6">
      <button @click="switchTab('ATTENDANCE')" :class="activeTab === 'ATTENDANCE' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'">
        📋 Feuille d'Appel Numérique & Absences
      </button>
      <button @click="switchTab('EVALUATIONS')" :class="activeTab === 'EVALUATIONS' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-3 font-extrabold' : 'text-gray-500 pb-3'">
        ⭐ Évaluations Trimestrielles par Compétences (0 à 5 ⭐)
      </button>
    </div>

    <!-- TAB 1: Feuille d'Appel Numérique -->
    <div v-if="activeTab === 'ATTENDANCE'" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4 dark:border-gray-700">
        <div>
          <h3 class="font-bold text-lg text-gray-900 dark:text-white">Validation de Présence par Classe</h3>
          <p class="text-xs text-gray-500">Sélectionnez la date et cochez le statut de chaque élève. Tout changement est sauvegardé en base de données et visible par l'élève et l'administration.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <select v-model="selectedClass" @change="loadAttendanceSheet" class="px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-bold text-emerald-700 dark:text-emerald-300">
            <option v-for="c in assignedClasses" :key="c.id" :value="c.name">{{ c.name }}</option>
          </select>

          <input type="date" v-model="attendanceDate" @change="loadAttendanceSheet" class="px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200" />

          <button @click="loadAttendanceSheet" class="p-2 rounded-xl border bg-gray-50 hover:bg-gray-100 text-gray-600 text-xs font-bold" title="Rafraîchir">
            🔄
          </button>
        </div>
      </div>

      <!-- Quick Summary Badges for current sheet -->
      <div class="flex flex-wrap items-center gap-3 text-xs font-bold">
        <span class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
          <span>🟢</span> Présents : {{ currentPresentsCount }}
        </span>
        <span class="px-3 py-1.5 rounded-xl bg-red-50 text-red-700 border border-red-200 flex items-center gap-1.5">
          <span>🔴</span> Absents : {{ currentAbsentsCount }}
        </span>
        <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5">
          <span>🟡</span> Retards : {{ currentLatesCount }}
        </span>
        <span class="text-gray-400 text-[11px] ml-auto">
          Séance du <strong>{{ formattedDate }}</strong>
        </span>
      </div>

      <div v-if="loadingAttendance" class="py-12 text-center text-xs font-bold text-gray-400">
        Chargement de la feuille d'appel depuis la base de données...
      </div>

      <!-- Liste des Élèves de la classe pour l'Appel -->
      <div v-else-if="studentsList.length === 0" class="py-12 text-center text-xs font-bold text-gray-400">
        Aucun élève trouvé pour cette classe.
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="student in studentsList"
          :key="student.id"
          class="p-4 rounded-2xl border transition-all flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
          :class="{
            'bg-emerald-50/40 border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-900/40': student.attendance === 'PRESENT',
            'bg-red-50/50 border-red-200 dark:bg-red-950/20 dark:border-red-900/40': student.attendance === 'ABSENT',
            'bg-amber-50/50 border-amber-200 dark:bg-amber-950/20 dark:border-amber-900/40': student.attendance === 'LATE'
          }"
        >
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-xl font-bold text-white text-xs flex items-center justify-center shadow"
              :class="{
                'bg-emerald-600': student.attendance === 'PRESENT',
                'bg-red-600': student.attendance === 'ABSENT',
                'bg-amber-500': student.attendance === 'LATE'
              }"
            >
              {{ student.name ? student.name[0] : 'É' }}
            </div>
            <div>
              <h4 class="font-bold text-gray-900 dark:text-white text-sm">{{ student.name }}</h4>
              <p class="text-xs text-gray-400">Parent: {{ student.parentName }} ({{ student.phone }})</p>
            </div>
          </div>

          <!-- Boutons de Statut Présence + Justification -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="student.attendance = 'PRESENT'"
                :class="student.attendance === 'PRESENT' ? 'bg-emerald-600 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 border hover:bg-emerald-50'"
                class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1"
              >
                <span>🟢</span> Présent
              </button>

              <button
                type="button"
                @click="student.attendance = 'ABSENT'"
                :class="student.attendance === 'ABSENT' ? 'bg-red-600 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 border hover:bg-red-50'"
                class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1"
              >
                <span>🔴</span> Absent
              </button>

              <button
                type="button"
                @click="student.attendance = 'LATE'"
                :class="student.attendance === 'LATE' ? 'bg-amber-500 text-white shadow' : 'bg-white dark:bg-gray-800 text-gray-600 border hover:bg-amber-50'"
                class="px-3 py-1.5 rounded-xl font-bold text-xs transition-all flex items-center gap-1"
              >
                <span>🟡</span> Retard
              </button>
            </div>

            <!-- Motif / Justification input for Absences or Lates -->
            <input
              v-if="student.attendance !== 'PRESENT'"
              v-model="student.justification"
              type="text"
              placeholder="Motif (ex: Maladie, Rendez-vous...)"
              class="px-3 py-1.5 rounded-xl border bg-white dark:bg-gray-800 text-xs w-full sm:w-48 text-gray-800 dark:text-gray-200"
            />
          </div>
        </div>
      </div>

      <div class="flex justify-between items-center pt-4 border-t dark:border-gray-700">
        <p class="text-[11px] text-gray-500">
          Les absences et retards validés ici seront immédiatement comptabilisés dans le dossier élève et consultables par l'administrateur.
        </p>
        <button
          @click="submitAttendance"
          :disabled="saving || loadingAttendance"
          class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center gap-2 disabled:opacity-50"
        >
          <span>💾</span> {{ saving ? 'Enregistrement en base...' : 'Valider & Enregistrer l\'Appel' }}
        </button>
      </div>
    </div>

    <!-- TAB 2: Saisie des Évaluations par Compétences (Valeur par défaut 0, Liste Paginée & Accordéon Rétractable) -->
    <div v-if="activeTab === 'EVALUATIONS'" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-6">
      <!-- Top Filters & Period Selector -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b pb-4 dark:border-gray-700">
        <div>
          <h3 class="font-bold text-lg text-gray-900 dark:text-white flex items-center gap-2">
            <span>⭐</span> Évaluations Trimestrielles par Compétences
          </h3>
          <p class="text-xs text-gray-500">
            Évaluez chaque élève (<strong>0 = Non évalué</strong>, 1 à 5 ⭐). Les dossiers se replient automatiquement une fois saisis pour garder une vue claire même avec de grands effectifs.
          </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <select v-model="selectedClass" @change="loadEvaluationSheet" class="px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-bold text-emerald-700 dark:text-emerald-300">
            <option v-for="c in assignedClasses" :key="c.id" :value="c.name">{{ c.name }}</option>
          </select>

          <select v-model="selectedEvaluationTerm" @change="loadEvaluationSheet" class="px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs font-bold text-gray-700 dark:text-gray-200">
            <option value="Trimestre 1">Trimestre 1</option>
            <option value="Trimestre 2">Trimestre 2</option>
            <option value="Trimestre 3">Trimestre 3</option>
            <option value="Premier Semestre">Premier Semestre</option>
            <option value="Deuxième Semestre">Deuxième Semestre</option>
          </select>

          <button @click="loadEvaluationSheet" class="p-2 rounded-xl border bg-gray-50 hover:bg-gray-100 text-gray-600 text-xs font-bold" title="Rafraîchir les évaluations">
            🔄
          </button>
        </div>
      </div>

      <!-- Toolbar: Search, Filter, Expand/Collapse All & Items per page -->
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3.5 bg-gray-50 dark:bg-gray-700/40 rounded-2xl border border-gray-200 dark:border-gray-700 text-xs">
        <div class="flex flex-wrap items-center gap-3 flex-grow max-w-xl">
          <!-- Search input -->
          <div class="relative flex-grow min-w-[160px]">
            <input
              v-model="evalSearch"
              type="text"
              placeholder="Rechercher un élève..."
              class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-emerald-500"
            />
            <span class="absolute left-2.5 top-2 text-gray-400 text-xs">🔍</span>
          </div>

          <!-- Status Filter -->
          <select
            v-model="evalStatusFilter"
            class="px-2.5 py-1.5 rounded-xl border bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300"
          >
            <option value="ALL">Tous les élèves ({{ evaluationStudentsList.length }})</option>
            <option value="EVALUATED">Évalués uniquement ({{ evaluatedStudentsCount }})</option>
            <option value="PENDING">En attente / Non évalués ({{ pendingStudentsCount }})</option>
          </select>
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <!-- Expand / Collapse All buttons -->
          <button
            type="button"
            @click="collapseAll"
            class="px-2.5 py-1.5 rounded-xl border bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 font-bold text-[11px] text-gray-700 dark:text-gray-300 transition-all flex items-center gap-1"
          >
            <span>📁</span> Tout replier
          </button>
          <button
            type="button"
            @click="expandAll"
            class="px-2.5 py-1.5 rounded-xl border bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 font-bold text-[11px] text-gray-700 dark:text-gray-300 transition-all flex items-center gap-1"
          >
            <span>📂</span> Tout déplier
          </button>

          <!-- Items per page selector -->
          <select
            v-model="itemsPerPage"
            class="px-2.5 py-1.5 rounded-xl border bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300"
          >
            <option :value="5">5 / page</option>
            <option :value="10">10 / page</option>
            <option :value="20">20 / page</option>
            <option :value="999">Tous ({{ evaluationStudentsList.length }})</option>
          </select>
        </div>
      </div>

      <!-- Quick Legend Info -->
      <div class="p-3 bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/40 rounded-2xl flex flex-wrap items-center justify-between text-xs text-emerald-900 dark:text-emerald-200 gap-2">
        <div class="flex flex-wrap items-center gap-2">
          <span class="font-bold text-[11px]">Échelle d'évaluation :</span>
          <span class="px-2 py-0.5 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-[10px] font-bold">0 = Non évalué</span>
          <span class="px-2 py-0.5 rounded-md bg-red-100 text-red-800 text-[10px] font-bold">1 ⭐ = Non acquis</span>
          <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 text-[10px] font-bold">2 ⭐ = À consolider</span>
          <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 text-[10px] font-bold">3 ⭐ = En cours</span>
          <span class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-800 text-[10px] font-bold">4 ⭐ = Bien acquis</span>
          <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-bold">5 ⭐ = Excellent</span>
        </div>
        <div class="text-[11px] font-extrabold text-emerald-700 dark:text-emerald-300">
          {{ evaluatedStudentsCount }} / {{ evaluationStudentsList.length }} élèves complétés
        </div>
      </div>

      <div v-if="loadingEvaluations" class="py-12 text-center text-xs font-bold text-gray-400">
        Chargement des évaluations et compétences depuis la base de données...
      </div>

      <div v-else-if="filteredEvaluationStudents.length === 0" class="py-12 text-center text-xs font-bold text-gray-400">
        Aucun élève ne correspond aux critères de filtre.
      </div>

      <!-- Accordion Students List -->
      <div v-else class="space-y-4">
        <div
          v-for="student in paginatedEvaluationStudents"
          :key="student.id"
          class="rounded-3xl border transition-all overflow-hidden"
          :class="isStudentCollapsed(student.id)
            ? 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-emerald-300 shadow-sm'
            : 'bg-gray-50/80 dark:bg-gray-800/90 border-emerald-500/40 shadow-md ring-1 ring-emerald-500/20'"
        >
          <!-- Collapsed Summary Bar (Always Visible, Clickable) -->
          <div
            @click="toggleStudentCollapse(student.id)"
            class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer select-none transition-colors hover:bg-gray-50/60 dark:hover:bg-gray-700/30"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-2xl font-bold flex items-center justify-center text-sm shadow shrink-0 text-white"
                :class="getRatedSkillsCount(student) > 0 ? 'bg-emerald-600' : 'bg-gray-400 dark:bg-gray-600'"
              >
                {{ student.name ? student.name[0] : 'É' }}
              </div>
              <div>
                <div class="flex items-center gap-2">
                  <h4 class="font-extrabold text-base text-gray-900 dark:text-white">{{ student.name }}</h4>
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                    :class="getRatedSkillsCount(student) === totalSkillsCount
                      ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'
                      : getRatedSkillsCount(student) > 0
                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-300'
                        : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'"
                  >
                    {{ getRatedSkillsCount(student) }}/{{ totalSkillsCount }} évalué(s)
                  </span>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">
                  {{ selectedClass }} • {{ selectedEvaluationTerm }}
                  <span v-if="student.teacherRemarks" class="italic text-gray-500 dark:text-gray-400 ml-2">
                    — "{{ student.teacherRemarks.slice(0, 40) }}..."
                  </span>
                </p>
              </div>
            </div>

            <!-- Average Badge + Fold/Unfold Indicator -->
            <div class="flex items-center gap-3 self-end sm:self-auto">
              <div
                class="px-3 py-1 rounded-xl text-xs font-black flex items-center gap-1.5"
                :class="getRatedSkillsCount(student) > 0
                  ? 'bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300 shadow-sm'
                  : 'bg-gray-100 dark:bg-gray-700 text-gray-400'"
              >
                <span>⭐</span>
                <span>{{ computeStudentAverage(student) }} / 5</span>
              </div>

              <button
                type="button"
                class="px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-600 font-bold text-xs flex items-center gap-1 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all"
              >
                <span v-if="isStudentCollapsed(student.id)">▼ Déplier</span>
                <span v-else>▲ Replier</span>
              </button>
            </div>
          </div>

          <!-- Expanded Body: Ratings Grid & Remarks Form -->
          <div v-show="!isStudentCollapsed(student.id)" class="p-6 pt-0 border-t border-gray-200 dark:border-gray-700 space-y-6">
            <!-- Subjects & Skills Interactive 0-5 Stars -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4">
              <div
                v-for="subject in curriculumList"
                :key="subject.id"
                class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm space-y-3"
              >
                <div class="flex items-center justify-between font-bold text-xs text-emerald-800 dark:text-emerald-300 border-b pb-2 dark:border-gray-700">
                  <div class="flex items-center gap-1.5">
                    <span class="text-base">{{ subject.icon || '📚' }}</span>
                    <span>{{ subject.name }}</span>
                  </div>
                  <span class="font-arabic font-semibold text-gray-500 text-xs">{{ subject.nameAr }}</span>
                </div>

                <div class="space-y-3">
                  <div v-for="skill in subject.skills" :key="skill.id" class="text-xs">
                    <div class="flex items-center justify-between mb-1 gap-2">
                      <span class="font-medium text-gray-700 dark:text-gray-300 text-[11px] leading-tight">
                        {{ skill.name }}
                      </span>
                      <span
                        class="font-bold text-[10px] shrink-0 px-2 py-0.5 rounded-md"
                        :class="getStarBadgeClass(student.ratings[skill.id] || 0)"
                      >
                        {{ getStarLabel(student.ratings[skill.id] || 0) }}
                      </span>
                    </div>

                    <!-- 0 to 5 Stars Interactive Selector -->
                    <div class="flex items-center gap-1.5">
                      <div class="flex items-center gap-0.5">
                        <button
                          v-for="star in 5"
                          :key="star"
                          type="button"
                          @click.stop="toggleSkillRating(student, skill.id, star)"
                          class="w-7 h-7 rounded-lg text-base font-bold flex items-center justify-center transition-all cursor-pointer focus:outline-none"
                          :class="star <= (student.ratings[skill.id] || 0)
                            ? 'text-amber-400 hover:scale-125'
                            : 'text-gray-300 dark:text-gray-600 hover:text-amber-200'"
                          :title="`Attribuer ${star} étoile(s)`"
                        >
                          ★
                        </button>
                      </div>

                      <!-- Reset to 0 button -->
                      <button
                        v-if="(student.ratings[skill.id] || 0) > 0"
                        type="button"
                        @click.stop="student.ratings[skill.id] = 0"
                        class="text-[10px] text-gray-400 hover:text-red-500 underline ml-1"
                        title="Réinitialiser à 0 (Non évalué)"
                      >
                        Effacer
                      </button>

                      <span class="text-[10px] text-gray-400 font-mono ml-auto font-bold">
                        {{ student.ratings[skill.id] || 0 }}/5
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Free text zone: Teacher Remarks -->
            <div class="pt-2">
              <label class="block font-bold text-xs text-gray-800 dark:text-gray-200 mb-1 flex items-center gap-1.5">
                <span>✍️</span> Remarques et appréciations de l'enseignant(e) :
              </label>
              <textarea
                v-model="student.teacherRemarks"
                rows="2"
                placeholder="Observations pédagogiques, assiduité, comportement et conseils pour le trimestre..."
                class="w-full px-3.5 py-2.5 rounded-2xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-xs text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-emerald-500"
              ></textarea>
            </div>

            <!-- Card Bottom Action: Save & Fold this student -->
            <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
              <button
                type="button"
                @click="toggleStudentCollapse(student.id)"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5"
              >
                <span>▲</span> Replier ce dossier
              </button>

              <button
                type="button"
                @click="saveAndCollapseSingle(student)"
                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-1.5"
              >
                <span>💾</span> Valider & Replier ce dossier
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination Controls -->
      <div v-if="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t dark:border-gray-700 text-xs">
        <span class="text-gray-500">
          Affichage des élèves {{ paginationStart + 1 }} à {{ Math.min(paginationEnd, filteredEvaluationStudents.length) }} sur {{ filteredEvaluationStudents.length }}
        </span>

        <div class="flex items-center gap-1.5">
          <button
            type="button"
            @click="currentPage--"
            :disabled="currentPage <= 1"
            class="px-3 py-1.5 rounded-xl border border-gray-300 dark:border-gray-600 font-bold disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            ← Précédent
          </button>

          <button
            v-for="page in totalPages"
            :key="page"
            type="button"
            @click="currentPage = page"
            class="w-8 h-8 rounded-xl font-bold text-xs transition-all"
            :class="currentPage === page ? 'bg-emerald-600 text-white shadow' : 'border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'"
          >
            {{ page }}
          </button>

          <button
            type="button"
            @click="currentPage++"
            :disabled="currentPage >= totalPages"
            class="px-3 py-1.5 rounded-xl border border-gray-300 dark:border-gray-600 font-bold disabled:opacity-40 hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            Suivant →
          </button>
        </div>
      </div>

      <!-- Global Save Footer -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4 border-t dark:border-gray-700">
        <p class="text-[11px] text-gray-500">
          Enregistre l'ensemble des évaluations de la classe en base de données. Les notes modifiées seront immédiatement disponibles sur les bulletins officiels PDF.
        </p>
        <button
          @click="submitEvaluations"
          :disabled="savingEvaluations || loadingEvaluations"
          class="px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 disabled:opacity-50 shrink-0"
        >
          <span>💾</span> {{ savingEvaluations ? 'Enregistrement en base...' : 'Enregistrer Toute la Classe & Replier' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth.store'
import apiClient from '../../plugins/axios'
import { showSuccessAlert, showErrorAlert } from '../../plugins/notify'

const authStore = useAuthStore()
const activeTab = ref('ATTENDANCE')

const teacherName = computed(() => {
  if (authStore.user?.email === 'mahmoud@tijanesnours.lu') return 'Cheikh Mahmoud'
  return authStore.user?.email || 'Enseignant'
})

const selectedClass = ref('Classe Débutant 2A (6-8 ans)')
const attendanceDate = ref(new Date().toISOString().split('T')[0])
const saving = ref(false)
const loadingAttendance = ref(false)

const assignedClasses = ref([])
const studentsList = ref([])

// Evaluation Tab States
const selectedEvaluationTerm = ref('Trimestre 1')
const loadingEvaluations = ref(false)
const savingEvaluations = ref(false)
const curriculumList = ref([])
const evaluationStudentsList = ref([])

// Pagination, Search & Accordion State
const evalSearch = ref('')
const evalStatusFilter = ref('ALL')
const itemsPerPage = ref(5)
const currentPage = ref(1)
const collapsedMap = ref({}) // studentId -> boolean

const formattedDate = computed(() => {
  if (!attendanceDate.value) return ''
  const parts = attendanceDate.value.split('-')
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`
  return attendanceDate.value
})

const currentPresentsCount = computed(() => {
  return studentsList.value.filter(s => s.attendance === 'PRESENT').length
})

const currentAbsentsCount = computed(() => {
  return studentsList.value.filter(s => s.attendance === 'ABSENT').length
})

const currentLatesCount = computed(() => {
  return studentsList.value.filter(s => s.attendance === 'LATE').length
})

const totalSkillsCount = computed(() => {
  let count = 0
  for (const sub of curriculumList.value) {
    count += (sub.skills || []).length
  }
  return count || 9
})

function getRatedSkillsCount(student) {
  if (!student.ratings) return 0
  let count = 0
  for (const val of Object.values(student.ratings)) {
    if (parseInt(val, 10) > 0) count++
  }
  return count
}

const evaluatedStudentsCount = computed(() => {
  return evaluationStudentsList.value.filter(s => getRatedSkillsCount(s) > 0).length
})

const pendingStudentsCount = computed(() => {
  return evaluationStudentsList.value.filter(s => getRatedSkillsCount(s) === 0).length
})

const filteredEvaluationStudents = computed(() => {
  return evaluationStudentsList.value.filter(s => {
    // 1. Search
    if (evalSearch.value.trim()) {
      const q = evalSearch.value.toLowerCase().trim()
      if (!s.name.toLowerCase().includes(q)) return false
    }

    // 2. Status
    const ratedCount = getRatedSkillsCount(s)
    if (evalStatusFilter.value === 'EVALUATED' && ratedCount === 0) return false
    if (evalStatusFilter.value === 'PENDING' && ratedCount > 0) return false

    return true
  })
})

const totalPages = computed(() => {
  if (itemsPerPage.value >= 999) return 1
  return Math.ceil(filteredEvaluationStudents.value.length / itemsPerPage.value) || 1
})

const paginationStart = computed(() => {
  return (currentPage.value - 1) * itemsPerPage.value
})

const paginationEnd = computed(() => {
  return paginationStart.value + itemsPerPage.value
})

const paginatedEvaluationStudents = computed(() => {
  if (itemsPerPage.value >= 999) return filteredEvaluationStudents.value
  return filteredEvaluationStudents.value.slice(paginationStart.value, paginationEnd.value)
})

function isStudentCollapsed(studentId) {
  return collapsedMap.value[studentId] === true
}

function toggleStudentCollapse(studentId) {
  collapsedMap.value[studentId] = !collapsedMap.value[studentId]
}

function collapseAll() {
  for (const s of evaluationStudentsList.value) {
    collapsedMap.value[s.id] = true
  }
}

function expandAll() {
  for (const s of evaluationStudentsList.value) {
    collapsedMap.value[s.id] = false
  }
}

function switchTab(tab) {
  activeTab.value = tab
  if (tab === 'EVALUATIONS') {
    loadEvaluationSheet()
  } else if (tab === 'ATTENDANCE') {
    loadAttendanceSheet()
  }
}

function getStarLabel(count) {
  const n = parseInt(count, 10) || 0
  if (n <= 0) return 'Non évalué'
  if (n === 1) return 'Non acquis'
  if (n === 2) return 'À consolider'
  if (n === 3) return 'En cours'
  if (n === 4) return 'Bien acquis'
  return 'Excellent'
}

function getStarBadgeClass(count) {
  const n = parseInt(count, 10) || 0
  if (n <= 0) return 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'
  if (n === 1) return 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300'
  if (n === 2) return 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300'
  if (n === 3) return 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300'
  if (n === 4) return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300'
  return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
}

function toggleSkillRating(student, skillId, star) {
  if (!student.ratings) student.ratings = {}
  // If clicking on the same star level, allow resetting to 0
  if (student.ratings[skillId] === star) {
    student.ratings[skillId] = 0
  } else {
    student.ratings[skillId] = star
  }
}

function computeStudentAverage(student) {
  if (!student.ratings) return '0.0'
  const values = Object.values(student.ratings).map(v => parseInt(v, 10) || 0).filter(v => v > 0)
  if (values.length === 0) return '0.0'
  const sum = values.reduce((acc, curr) => acc + curr, 0)
  return (sum / values.length).toFixed(1)
}

async function fetchClasses() {
  try {
    const res = await apiClient.get('/teacher/classes')
    if (Array.isArray(res.data) && res.data.length > 0) {
      assignedClasses.value = res.data
      if (!assignedClasses.value.some(c => c.name === selectedClass.value)) {
        selectedClass.value = assignedClasses.value[0].name
      }
    }
  } catch (err) {
    console.error('Erreur chargement des classes enseignant:', err)
  }
}

async function loadAttendanceSheet() {
  loadingAttendance.value = true
  try {
    const res = await apiClient.get('/teacher/attendance', {
      params: {
        class_name: selectedClass.value,
        date: attendanceDate.value
      }
    })

    if (res.data && Array.isArray(res.data.students)) {
      studentsList.value = res.data.students
    }
  } catch (err) {
    console.error('Erreur chargement feuille appel:', err)
  } finally {
    loadingAttendance.value = false
  }
}

async function submitAttendance() {
  saving.value = true
  try {
    const payload = {
      className: selectedClass.value,
      date: attendanceDate.value,
      attendances: studentsList.value.map(st => ({
        id: st.id,
        attendance: st.attendance,
        justification: st.justification || ''
      }))
    }

    const res = await apiClient.post('/teacher/attendance', payload)
    const stats = res.data.stats || {}

    showSuccessAlert(
      'Feuille d\'Appel Enregistrée ! 🎉',
      `La feuille d'appel du <strong>${formattedDate.value}</strong> pour <strong>${selectedClass.value}</strong> a été enregistrée en base de données.<br>` +
      `<span class="badge bg-emerald-100 text-emerald-800 me-1">🟢 ${stats.presents || 0} Présent(s)</span> ` +
      `<span class="badge bg-red-100 text-red-800 me-1">🔴 ${stats.absents || 0} Absent(s)</span> ` +
      `<span class="badge bg-amber-100 text-amber-800">🟡 ${stats.lates || 0} Retard(s)</span>`
    )

    await loadAttendanceSheet()
  } catch (err) {
    console.error('Erreur enregistrement feuille appel:', err)
    showErrorAlert('Erreur', 'Erreur lors de la validation de la feuille d\'appel en base de données.')
  } finally {
    saving.value = false
  }
}

async function loadEvaluationSheet() {
  loadingEvaluations.value = true
  try {
    const res = await apiClient.get('/teacher/evaluations', {
      params: {
        class_name: selectedClass.value,
        term: selectedEvaluationTerm.value,
        school_year: '2026-2027'
      }
    })

    if (res.data) {
      curriculumList.value = res.data.curriculum || []
      const loaded = (res.data.students || []).map(st => ({
        ...st,
        ratings: st.ratings || {},
        teacherRemarks: st.teacherRemarks || ''
      }))

      evaluationStudentsList.value = loaded

      // By default: collapse students who already have complete evaluations, leave others unfolded or collapse all if many
      const newCollapsed = {}
      for (const s of loaded) {
        const rated = getRatedSkillsCount(s)
        // If already evaluated, fold by default; if not, unfold so teacher can easily fill
        newCollapsed[s.id] = (rated > 0)
      }
      collapsedMap.value = newCollapsed
      currentPage.value = 1
    }
  } catch (err) {
    console.error('Erreur chargement des évaluations enseignant:', err)
  } finally {
    loadingEvaluations.value = false
  }
}

async function saveAndCollapseSingle(student) {
  try {
    const payload = {
      className: selectedClass.value,
      term: selectedEvaluationTerm.value,
      schoolYear: '2026-2027',
      evaluations: [
        {
          id: student.id,
          studentId: student.id,
          ratings: student.ratings,
          teacherRemarks: student.teacherRemarks
        }
      ]
    }

    await apiClient.post('/teacher/evaluations', payload)
    // Collapse this student immediately
    collapsedMap.value[student.id] = true
    showSuccessAlert('Évaluation Enregistrée ! ⭐', `Le carnet de <strong>${student.name}</strong> a été validé et replié.`)
  } catch (err) {
    console.error('Erreur sauvegarde élève:', err)
    showErrorAlert('Erreur', 'Impossible de sauvegarder l\'évaluation.')
  }
}

async function submitEvaluations() {
  savingEvaluations.value = true
  try {
    const payload = {
      className: selectedClass.value,
      term: selectedEvaluationTerm.value,
      schoolYear: '2026-2027',
      evaluations: evaluationStudentsList.value.map(st => ({
        id: st.id,
        studentId: st.id,
        ratings: st.ratings,
        teacherRemarks: st.teacherRemarks
      }))
    }

    const res = await apiClient.post('/teacher/evaluations', payload)

    showSuccessAlert(
      'Évaluations Trimestrielles Enregistrées ! 🌟',
      `Le carnet d'évaluations de la <strong>${selectedClass.value}</strong> (${selectedEvaluationTerm.value}) a été enregistré pour <strong>${res.data.savedCount || evaluationStudentsList.value.length}</strong> élève(s).`
    )

    // Collapse all students after saving
    collapseAll()
  } catch (err) {
    console.error('Erreur enregistrement évaluations:', err)
    showErrorAlert('Erreur', 'Erreur lors de l\'enregistrement des évaluations en base de données.')
  } finally {
    savingEvaluations.value = false
  }
}

onMounted(async () => {
  await fetchClasses()
  await loadAttendanceSheet()
})
</script>

<style scoped>
.font-arabic {
  font-family: 'Amiri', 'Noto Naskh Arabic', serif;
}
</style>
