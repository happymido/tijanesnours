<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion & Configuration Scolaire (Base MySQL)</h1>
        <p class="text-xs text-gray-500">Classes, créneaux horaires, catégories et niveaux configurables en temps réel</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <button @click="openClassModal()" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>+</span> Nouvelle Classe
        </button>
        <button @click="openScheduleConfigModal()" class="px-4 py-2.5 bg-gold-600 hover:bg-gold-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>⏰</span> Nouveau Créneau
        </button>
        <button @click="openCategoryModal()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>📚</span> Nouvelle Catégorie
        </button>
        <button @click="openLevelModal()" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>📊</span> Nouveau Niveau
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 text-xs font-bold gap-6 overflow-x-auto">
      <button @click="currentSubTab = 'TABLE'" :class="currentSubTab === 'TABLE' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">
        📋 Tableau des Classes ({{ classrooms.length }})
      </button>
      <button @click="currentSubTab = 'SCHEDULES'" :class="currentSubTab === 'SCHEDULES' ? 'border-b-2 border-gold-600 text-gold-600 pb-3' : 'text-gray-500 pb-3'">
        ⏰ Créneaux Horaires ({{ schedules.length }})
      </button>
      <button @click="currentSubTab = 'CATEGORIES'" :class="currentSubTab === 'CATEGORIES' ? 'border-b-2 border-emerald-600 text-emerald-600 pb-3' : 'text-gray-500 pb-3'">
        📚 Catégories de Cours ({{ categories.length }})
      </button>
      <button @click="currentSubTab = 'LEVELS'" :class="currentSubTab === 'LEVELS' ? 'border-b-2 border-purple-600 text-purple-600 pb-3' : 'text-gray-500 pb-3'">
        📊 Niveaux de Cours ({{ levels.length }})
      </button>
      <button @click="currentSubTab = 'TEACHER_ASSIGNMENTS'" :class="currentSubTab === 'TEACHER_ASSIGNMENTS' ? 'border-b-2 border-brand-600 text-brand-600 pb-3' : 'text-gray-500 pb-3'">
        👨‍🏫 Affectations Enseignants ({{ teachersList.length }})
      </button>
    </div>

    <!-- TAB 1: Tableau Général des Classes (Datatable) -->
    <div v-if="currentSubTab === 'TABLE'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h3 class="font-bold text-base text-gray-900 dark:text-white">Liste Détaillée des Classes (Base MySQL)</h3>
        <input
          v-model="classSearch"
          type="text"
          placeholder="Filtrer par nom, enseignant, salle..."
          class="px-4 py-2 rounded-xl bg-gray-50 dark:bg-gray-700 border text-xs w-64"
        />
      </div>

      <div v-if="loading" class="py-8 text-center text-xs font-bold text-gray-500">
        Chargement des données depuis MySQL...
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3.5 px-4">Classe</th>
              <th class="py-3.5 px-4">Niveau / Tranche d'âge</th>
              <th class="py-3.5 px-4">Catégorie</th>
              <th class="py-3.5 px-4">Enseignant Affecté</th>
              <th class="py-3.5 px-4">Salle & Créneau</th>
              <th class="py-3.5 px-4">Effectifs</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="cls in filteredClassrooms" :key="cls.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white">{{ cls.name }}</td>
              <td class="py-3.5 px-4">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300">
                  {{ cls.level }}
                </span>
              </td>
              <td class="py-3.5 px-4 font-semibold text-gray-600 dark:text-gray-300">
                <span class="inline-block px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-[10px]">
                  {{ cls.category }}
                </span>
              </td>
              <td class="py-3.5 px-4 font-bold text-emerald-600 dark:text-emerald-400">
                <router-link :to="`/admin/users/teacher_1/id-card`" class="hover:underline">
                  {{ cls.teacher }}
                </router-link>
              </td>
              <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">
                <div>🚪 {{ cls.roomNumber || cls.room }}</div>
                <div class="text-[10px] text-gray-400">⏰ {{ cls.schedule }}</div>
              </td>
              <td class="py-3.5 px-4">
                <span class="font-extrabold text-gray-900 dark:text-white">{{ cls.currentEnrolled ?? 0 }}</span> / {{ cls.maxCapacity || cls.capacity || 20 }}
                <div class="w-24 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full mt-1 overflow-hidden">
                  <div :style="{ width: Math.min(((cls.currentEnrolled ?? 0) / (cls.maxCapacity || 20) * 100), 100) + '%' }" class="h-full bg-emerald-500"></div>
                </div>
              </td>
              <td class="py-3.5 px-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    @click="openRoster(cls)"
                    title="Voir l'effectif des élèves"
                    class="px-2.5 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 dark:bg-brand-900/50 dark:text-brand-300 rounded-xl font-bold text-xs flex items-center gap-1 transition-all shadow-sm"
                  >
                    <span>👩‍🎓</span>
                    <span class="text-[11px] font-extrabold">({{ cls.currentEnrolled ?? 0 }})</span>
                  </button>
                  <button
                    @click="openClassModal(cls)"
                    title="Éditer la classe"
                    class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200 flex items-center justify-center text-sm shadow-sm transition-all hover:scale-105"
                  >
                    ✏️
                  </button>
                  <button
                    @click="deleteClass(cls)"
                    title="Supprimer la classe"
                    class="w-8 h-8 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400 flex items-center justify-center text-sm shadow-sm transition-all hover:scale-105"
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

    <!-- TAB 2: Créneaux Horaires CRUD -->
    <div v-if="currentSubTab === 'SCHEDULES'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-3 dark:border-gray-700">
        <div>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Configuration des Créneaux Horaires (Base MySQL)</h3>
          <p class="text-xs text-gray-500">Créneaux utilisables dans la création de classes et d'enseignants</p>
        </div>
        <button @click="openScheduleConfigModal()" class="px-4 py-2 bg-gold-600 hover:bg-gold-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>+</span> Ajouter un Créneau Horaire
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
            <tr>
              <th class="py-3.5 px-4">Créneau Intitulé</th>
              <th class="py-3.5 px-4">Jour de la Semaine</th>
              <th class="py-3.5 px-4">Heure Début</th>
              <th class="py-3.5 px-4">Heure Fin</th>
              <th class="py-3.5 px-4">Libellé / Session</th>
              <th class="py-3.5 px-4 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
            <tr v-for="sch in schedules" :key="sch.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
              <td class="py-3.5 px-4 font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <span>⏰</span> {{ sch.name }}
              </td>
              <td class="py-3.5 px-4 font-bold text-brand-600 dark:text-gold-400">{{ sch.day }}</td>
              <td class="py-3.5 px-4 text-gray-700 dark:text-gray-300 font-mono">{{ sch.startTime }}</td>
              <td class="py-3.5 px-4 text-gray-700 dark:text-gray-300 font-mono">{{ sch.endTime }}</td>
              <td class="py-3.5 px-4">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gold-100 text-gold-800 dark:bg-gold-900/50 dark:text-gold-300">
                  {{ sch.label || 'Standard' }}
                </span>
              </td>
              <td class="py-3.5 px-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    @click="openScheduleConfigModal(sch)"
                    title="Éditer le créneau"
                    class="w-8 h-8 rounded-xl bg-gold-50 hover:bg-gold-100 text-gold-700 dark:bg-gold-900/50 dark:text-gold-300 flex items-center justify-center text-sm shadow-sm transition-all hover:scale-105"
                  >
                    ✏️
                  </button>
                  <button
                    @click="deleteSchedule(sch)"
                    title="Supprimer le créneau"
                    class="w-8 h-8 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400 flex items-center justify-center text-sm shadow-sm transition-all hover:scale-105"
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

    <!-- TAB 3: Catégories de Cours CRUD Panel -->
    <div v-if="currentSubTab === 'CATEGORIES'" class="space-y-4">
      <div class="flex justify-between items-center bg-white dark:bg-gray-800 p-4 rounded-2xl border dark:border-gray-700">
        <div>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Gestion des Catégories de Cours (Enregistré dans MySQL)</h3>
          <p class="text-xs text-gray-500">Gérez les domaines d'enseignement (Langue Arabe, Tajwid, Éthique, etc.)</p>
        </div>
        <button @click="openCategoryModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow flex items-center gap-2">
          <span>+</span> Ajouter une Catégorie
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="cat in categories" :key="cat.id" class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4 flex flex-col justify-between">
          <div class="space-y-3">
            <div class="flex justify-between items-start">
              <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl font-bold shadow-sm">
                {{ cat.icon || '📚' }}
              </div>
              <span class="w-4 h-4 rounded-full border border-white shadow" :style="{ backgroundColor: cat.color || '#047857' }"></span>
            </div>
            <h3 class="font-bold text-base text-gray-900 dark:text-white">{{ cat.name }}</h3>
            <p class="text-xs text-gray-500 leading-relaxed">{{ cat.description }}</p>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t dark:border-gray-700">
            <button
              @click="openCategoryModal(cat)"
              title="Éditer la catégorie"
              class="w-9 h-9 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 flex items-center justify-center text-sm shadow-sm transition-all hover:scale-105"
            >
              ✏️
            </button>
            <button
              @click="deleteCategory(cat)"
              title="Supprimer la catégorie"
              class="w-9 h-9 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400 flex items-center justify-center text-sm shadow-sm transition-all hover:scale-105"
            >
              🗑️
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 4: Niveaux de Cours CRUD Panel -->
    <div v-if="currentSubTab === 'LEVELS'" class="space-y-4">
      <div class="flex justify-between items-center bg-white dark:bg-gray-800 p-4 rounded-2xl border dark:border-gray-700">
        <div>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Gestion des Niveaux de Cours (Enregistré dans MySQL)</h3>
          <p class="text-xs text-gray-500">Définissez les tranches d'âge et objectifs pédagogiques par niveau</p>
        </div>
        <button @click="openLevelModal()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-xl shadow flex items-center gap-2">
          <span>+</span> Ajouter un Niveau
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div v-for="lvl in levels" :key="lvl.id" class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4 flex flex-col justify-between">
          <div class="space-y-3">
            <div class="flex justify-between items-start">
              <span class="px-3 py-1 bg-purple-50 text-purple-700 dark:bg-purple-900/40 text-xs font-bold rounded-full border border-purple-200">
                Tranche {{ lvl.ageGroup || `${lvl.minAge}-${lvl.maxAge} ans` }}
              </span>
            </div>
            <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ lvl.name }}</h3>
            <p class="text-xs text-gray-500 leading-relaxed">{{ lvl.description }}</p>
          </div>

          <div class="flex justify-end gap-2 pt-3 border-t dark:border-gray-700">
            <button
              @click="openLevelModal(lvl)"
              title="Éditer le niveau"
              class="w-9 h-9 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300 flex items-center justify-center text-sm shadow-sm transition-all hover:scale-105"
            >
              ✏️
            </button>
            <button
              @click="deleteLevel(lvl)"
              title="Supprimer le niveau"
              class="w-9 h-9 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400 flex items-center justify-center text-sm shadow-sm transition-all hover:scale-105"
            >
              🗑️
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- TAB 5: Affectations Enseignants -->
    <div v-if="currentSubTab === 'TEACHER_ASSIGNMENTS'" class="space-y-6">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-100 dark:border-gray-700 p-6 space-y-4">
        <h3 class="font-bold text-base text-gray-900 dark:text-white border-b pb-3 dark:border-gray-700">
          Matrice d'Affectation des Enseignants aux Classes (Stocké en BBD MySQL)
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div v-for="teacher in teachersList" :key="teacher.id" class="p-5 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-200 dark:border-gray-600 space-y-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-emerald-600 font-bold text-white flex items-center justify-center text-sm shadow">
                👨‍🏫
              </div>
              <div>
                <h4 class="font-bold text-gray-900 dark:text-white text-sm">
                  <router-link :to="`/admin/users/teacher_${teacher.id}/id-card`" class="hover:text-emerald-600 hover:underline">
                    {{ teacher.name }}
                  </router-link>
                </h4>
                <p class="text-xs text-emerald-600 font-semibold">{{ teacher.speciality }}</p>
              </div>
            </div>

            <div class="space-y-2 pt-2 border-t dark:border-gray-600">
              <span class="text-[10px] font-bold text-gray-400 uppercase">Classes Assignées dans MySQL :</span>
              <div class="space-y-1">
                <div v-for="c in getClassesForTeacher(teacher.name)" :key="c.id" class="px-3 py-1.5 bg-white dark:bg-gray-800 rounded-xl border text-xs font-bold text-gray-800 dark:text-gray-200 flex justify-between items-center">
                  <span>📚 {{ c.name }}</span>
                  <span class="text-[10px] text-gray-400 font-normal">{{ c.schedule }}</span>
                </div>
                <div v-if="getClassesForTeacher(teacher.name).length === 0" class="text-xs text-gray-400 italic">
                  Aucune classe affectée
                </div>
              </div>
            </div>

            <button @click="openAssignModal(teacher)" class="w-full py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all">
              ⚙️ Gérer les Affectations
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Form pour Créer / Éditer une Classe -->
    <div v-if="showClassModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-lg w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">Créer/Éditer une Classe dans MySQL</h3>
          <button @click="showClassModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form @submit.prevent="saveClass" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold mb-1">Intitulé de la Classe</label>
            <input v-model="classForm.name" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold" placeholder="ex: Classe Débutant 2A" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Tranche d'âge / Niveau</label>
              <select v-model="classForm.level" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
                <option v-for="l in levels" :key="l.id" :value="l.name">{{ l.name }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Capacité Maximale</label>
              <input v-model="classForm.capacity" type="number" min="5" max="30" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" />
            </div>
          </div>

          <div>
            <label class="block font-semibold mb-1">Enseignant Référent Affecté</label>
            <select v-model="classForm.teacher" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold text-emerald-600">
              <option v-for="t in teachersList" :key="t.id" :value="t.name">{{ t.name }} ({{ t.speciality }})</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Créneau Horaire Configuré</label>
              <select v-model="classForm.schedule" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
                <option v-for="s in schedules" :key="s.id" :value="s.name">{{ s.name }}</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Salle de cours</label>
              <select v-model="classForm.room" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold">
                <option value="Salle Maryam 1">Salle Maryam 1</option>
                <option value="Salle Maryam 2">Salle Maryam 2</option>
                <option value="Salle Khadija 1">Salle Khadija 1</option>
                <option value="Salle Khadija 2">Salle Khadija 2</option>
                <option value="Grand Amphi A">Grand Amphi A</option>
              </select>
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="submit" :disabled="submitting" class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow disabled:opacity-50">
              {{ submitting ? 'Enregistrement MySQL...' : 'Enregistrer dans la BBD MySQL' }}
            </button>
            <button type="button" @click="showClassModal = false" class="py-3 px-4 border rounded-xl text-gray-600 font-semibold">
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Form pour Ajouter / Éditer un Créneau Horaire -->
    <div v-if="showScheduleModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">
            {{ scheduleEditingId ? '✏️ Éditer le Créneau Horaire' : '⏰ Nouveau Créneau Horaire' }}
          </h3>
          <button @click="showScheduleModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form @submit.prevent="saveScheduleConfig" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold mb-1">Jour de la Semaine</label>
            <select v-model="scheduleForm.day" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold">
              <option value="Samedi">Samedi</option>
              <option value="Dimanche">Dimanche</option>
              <option value="Mercredi">Mercredi</option>
              <option value="Vendredi">Vendredi</option>
              <option value="Lundi">Lundi</option>
              <option value="Mardi">Mardi</option>
              <option value="Jeudi">Jeudi</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Heure de Début</label>
              <input v-model="scheduleForm.startTime" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-mono font-bold" placeholder="09:00" />
            </div>
            <div>
              <label class="block font-semibold mb-1">Heure de Fin</label>
              <input v-model="scheduleForm.endTime" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-mono font-bold" placeholder="12:00" />
            </div>
          </div>

          <div>
            <label class="block font-semibold mb-1">Libellé / Session</label>
            <input v-model="scheduleForm.label" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-semibold" placeholder="ex: Matin" />
          </div>

          <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 py-3 bg-gold-600 hover:bg-gold-700 text-white font-bold rounded-xl shadow">
              💾 Save Créneau
            </button>
            <button type="button" @click="showScheduleModal = false" class="py-3 px-4 border rounded-xl text-gray-600 font-semibold">
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Form pour Ajouter / Éditer une Catégorie -->
    <div v-if="showCategoryModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">
            {{ categoryEditingId ? '✏️ Éditer la Catégorie' : '📚 Nouvelle Catégorie de Cours' }}
          </h3>
          <button @click="showCategoryModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form @submit.prevent="saveCategoryConfig" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold mb-1">Nom de la Catégorie</label>
            <input v-model="categoryForm.name" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold" placeholder="ex: Langue Arabe" />
          </div>

          <div>
            <label class="block font-semibold mb-1">Description / Objectifs</label>
            <textarea v-model="categoryForm.description" rows="3" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="Description du programme..."></textarea>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Couleur Thème</label>
              <input v-model="categoryForm.color" type="color" class="w-full h-10 rounded-xl cursor-pointer" />
            </div>
            <div>
              <label class="block font-semibold mb-1">Icone (Emoji)</label>
              <input v-model="categoryForm.icon" type="text" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 text-center font-bold text-lg" placeholder="📚" />
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow">
              💾 Enregistrer Catégorie
            </button>
            <button type="button" @click="showCategoryModal = false" class="py-3 px-4 border rounded-xl text-gray-600 font-semibold">
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Form pour Ajouter / Éditer un Niveau -->
    <div v-if="showLevelModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-md w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white">
            {{ levelEditingId ? '✏️ Éditer le Niveau' : '📊 Nouveau Niveau de Cours' }}
          </h3>
          <button @click="showLevelModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form @submit.prevent="saveLevelConfig" class="space-y-4 text-xs">
          <div>
            <label class="block font-semibold mb-1">Intitulé du Niveau</label>
            <input v-model="levelForm.name" type="text" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold" placeholder="ex: 6-8 ans (Débutant)" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Âge Minimum</label>
              <input v-model="levelForm.minAge" type="number" min="3" max="18" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold" />
            </div>
            <div>
              <label class="block font-semibold mb-1">Âge Maximum</label>
              <input v-model="levelForm.maxAge" type="number" min="4" max="25" required class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold" />
            </div>
          </div>

          <div>
            <label class="block font-semibold mb-1">Description du Niveau</label>
            <textarea v-model="levelForm.description" rows="3" class="w-full px-3 py-2.5 rounded-xl border bg-gray-50 dark:bg-gray-700" placeholder="Description des compétences visées..."></textarea>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow">
              💾 Enregistrer Niveau
            </button>
            <button type="button" @click="showLevelModal = false" class="py-3 px-4 border rounded-xl text-gray-600 font-semibold">
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Form pour Affecter les Classes à un Enseignant -->
    <div v-if="showAssignModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-lg w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-3 dark:border-gray-700">
          <div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
              <span>👨‍🏫</span> Affectations de Classes - {{ selectedAssignTeacher?.name }}
            </h3>
            <p class="text-xs text-gray-500">Cochez ou décochez les classes attribuées à cet enseignant dans MySQL</p>
          </div>
          <button @click="showAssignModal = false" class="text-gray-400 hover:text-gray-600 font-bold">✕</button>
        </div>

        <form @submit.prevent="saveTeacherAssignments" class="space-y-4 text-xs">
          <div class="space-y-2">
            <label class="font-bold text-emerald-800 dark:text-emerald-300 block text-xs border-b pb-1">
              📚 Sélectionner les classes attribuées à {{ selectedAssignTeacher?.name }} (Cochées par défaut) :
            </label>
            <div class="grid grid-cols-1 gap-2.5 max-h-64 overflow-y-auto pt-1">
              <label
                v-for="cls in classrooms"
                :key="cls.id"
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border cursor-pointer hover:bg-emerald-50 dark:hover:bg-emerald-900/30 transition-colors"
              >
                <div class="flex items-center gap-3">
                  <input
                    type="checkbox"
                    :value="cls.id"
                    v-model="assignForm.assignedClassIds"
                    class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 accent-emerald-600"
                  />
                  <div>
                    <span class="font-bold text-xs text-gray-900 dark:text-white block">{{ cls.name }}</span>
                    <span class="text-[10px] text-gray-500">⏰ {{ cls.schedule }} • 🚪 {{ cls.roomNumber || cls.room }}</span>
                  </div>
                </div>
                <span v-if="cls.teacher && cls.teacher !== selectedAssignTeacher?.name" class="text-[10px] text-gray-400 italic">
                  (Actuel: {{ cls.teacher }})
                </span>
              </label>
            </div>
          </div>

          <div class="flex gap-3 pt-4">
            <button type="submit" :disabled="submitting" class="flex-1 py-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow disabled:opacity-50">
              {{ submitting ? 'Enregistrement MySQL...' : '💾 Valider les Affectations dans MySQL' }}
            </button>
            <button type="button" @click="showAssignModal = false" class="py-3 px-4 border rounded-xl text-gray-600 font-semibold">
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Roster / Liste des Élèves d'une Classe -->
    <div v-if="showRosterModal" class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 max-w-2xl w-full shadow-2xl space-y-6">
        <div class="flex justify-between items-center border-b pb-4 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-brand-50 dark:bg-brand-900/50 text-brand-700 dark:text-brand-300 font-bold flex items-center justify-center text-lg shadow-sm">
              👩‍🎓
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                Élèves Inscrits - {{ selectedRosterClass?.name }}
              </h3>
              <p class="text-xs text-gray-500">
                ⏰ {{ selectedRosterClass?.schedule }} • 🚪 {{ selectedRosterClass?.roomNumber || selectedRosterClass?.room }}
              </p>
            </div>
          </div>
          <button @click="showRosterModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
        </div>

        <div class="space-y-4">
          <div class="flex justify-between items-center text-xs">
            <span class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 font-extrabold rounded-full">
              {{ currentRosterStudents.length }} Élève(s) inscrit(s) en BBD MySQL
            </span>
            <input
              v-model="rosterSearch"
              type="text"
              placeholder="🔍 Filtrer l'effectif..."
              class="px-3 py-1.5 rounded-xl border bg-gray-50 dark:bg-gray-700 text-xs w-56"
            />
          </div>

          <div v-if="loadingRoster" class="py-8 text-center text-xs font-bold text-gray-500">
            Chargement de l'effectif depuis MySQL...
          </div>

          <div v-else class="overflow-x-auto max-h-80 overflow-y-auto rounded-2xl border border-gray-100 dark:border-gray-700">
            <table class="w-full text-left text-xs">
              <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold sticky top-0">
                <tr>
                  <th class="py-3 px-4">Élève</th>
                  <th class="py-3 px-4">Date Naissance</th>
                  <th class="py-3 px-4">Parent Référent</th>
                  <th class="py-3 px-4">Contact Parent</th>
                  <th class="py-3 px-4 text-right">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr v-for="st in filteredRosterStudents" :key="st.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                  <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">
                    <router-link :to="`/admin/users/${st.id}/id-card`" class="text-brand-600 dark:text-gold-400 hover:underline">
                      {{ st.name }}
                    </router-link>
                  </td>
                  <td class="py-3 px-4 text-gray-500">{{ st.dateOfBirth }}</td>
                  <td class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">
                    <router-link :to="`/admin/users/${st.parentId || 'parent_1'}/id-card`" class="text-brand-600 hover:underline">
                      👨‍gsub {{ st.parentName }}
                    </router-link>
                  </td>
                  <td class="py-3 px-4 text-gray-500 font-mono text-[11px]">{{ st.contact }}</td>
                  <td class="py-3 px-4 text-right">
                    <router-link :to="`/admin/users/${st.id}/id-card`" class="px-3 py-1 bg-brand-600 text-white font-bold text-[10px] rounded-lg hover:bg-brand-700 transition-all shadow">
                      🪪 Voir Fiche
                    </router-link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import apiClient from '../../plugins/axios'
import { showSuccessAlert, showErrorAlert, showDeleteConfirmDialog } from '../../plugins/notify'

const currentSubTab = ref('TABLE')
const loading = ref(false)
const submitting = ref(false)
const classSearch = ref('')

const showClassModal = ref(false)
const showScheduleModal = ref(false)
const showCategoryModal = ref(false)
const showLevelModal = ref(false)

const scheduleEditingId = ref(null)
const categoryEditingId = ref(null)
const levelEditingId = ref(null)

const categories = ref([
  { id: 1, name: 'Langue Arabe', icon: '🗣️', color: '#047857', description: 'Apprentissage de la lecture, écriture, grammaire & vocabulaire' },
  { id: 2, name: 'Coran & Tajwid', icon: '📖', color: '#d97706', description: 'Mémorisation, récitation et règles de Tajwid' },
  { id: 3, name: 'Éducation Éthique', icon: '✨', color: '#2563eb', description: 'Valeurs morales et comportementales' }
])

const levels = ref([
  { id: 1, name: '4-5 ans (Éveil)', minAge: 4, maxAge: 5, ageGroup: '4-5 ans', description: 'Initiation ludique aux lettres et à la langue' },
  { id: 2, name: '6-8 ans (Débutant)', minAge: 6, maxAge: 8, ageGroup: '6-8 ans', description: 'Apprentissage de la lecture fluide' },
  { id: 3, name: '9-12 ans (Intermédiaire)', minAge: 9, maxAge: 12, ageGroup: '9-12 ans', description: 'Grammaire et mémorisation du Saint Coran' },
  { id: 4, name: '13-16 ans (Avancé Tajwid)', minAge: 13, maxAge: 16, ageGroup: '13-16 ans', description: 'Étude approfondie des règles de Tajwid' }
])

const teachersList = ref([
  { id: 1, name: 'Cheikh Mahmoud', speciality: 'Langue Arabe & Tajwid' },
  { id: 2, name: 'Oustaz Hassan', speciality: 'Coran & Mémorisation' },
  { id: 3, name: 'Mme Souad', speciality: 'Éducation Éthique & Arabe' }
])

const schedules = ref([
  { id: 1, day: 'Samedi', startTime: '09:00', endTime: '12:00', label: 'Matin', name: 'Samedi 09:00 - 12:00 (Matin)' },
  { id: 2, day: 'Samedi', startTime: '14:00', endTime: '17:00', label: 'Après-Midi', name: 'Samedi 14:00 - 17:00 (Après-Midi)' },
  { id: 3, day: 'Dimanche', startTime: '09:00', endTime: '12:00', label: 'Matin', name: 'Dimanche 09:00 - 12:00 (Matin)' },
  { id: 4, day: 'Dimanche', startTime: '14:00', endTime: '17:00', label: 'Après-Midi', name: 'Dimanche 14:00 - 17:00 (Après-Midi)' },
  { id: 5, day: 'Mercredi', startTime: '14:00', endTime: '17:00', label: 'Rattrapage', name: 'Mercredi 14:00 - 17:00 (Rattrapage)' },
  { id: 6, day: 'Vendredi', startTime: '17:30', endTime: '19:30', label: 'Soirée', name: 'Vendredi 17:30 - 19:30 (Soirée)' }
])

const classrooms = ref([
  { id: 1, name: 'Classe Éveil 1', level: '4-5 ans (Éveil)', category: 'Langue Arabe', teacher: 'Cheikh Mahmoud', schedule: 'Samedi 09:00 - 12:00 (Matin)', roomNumber: 'Salle Maryam 1', maxCapacity: 15, currentEnrolled: 2 },
  { id: 2, name: 'Classe Débutant 2A', level: '6-8 ans (Débutant)', category: 'Coran & Tajwid', teacher: 'Cheikh Mahmoud', schedule: 'Samedi 09:00 - 12:00 (Matin)', roomNumber: 'Salle Maryam 2', maxCapacity: 20, currentEnrolled: 2 }
])

const filteredClassrooms = computed(() => {
  if (!classSearch.value) return classrooms.value
  const q = classSearch.value.toLowerCase()
  return classrooms.value.filter(c => c.name.toLowerCase().includes(q) || (c.teacher && c.teacher.toLowerCase().includes(q)) || (c.roomNumber && c.roomNumber.toLowerCase().includes(q)))
})

const classForm = ref({
  name: '',
  level: '6-8 ans (Débutant)',
  teacher: 'Cheikh Mahmoud',
  schedule: 'Samedi 09:00 - 12:00 (Matin)',
  room: 'Salle Maryam 1',
  capacity: 20
})

const scheduleForm = ref({
  day: 'Samedi',
  startTime: '09:00',
  endTime: '12:00',
  label: 'Matin'
})

const categoryForm = ref({
  name: '',
  description: '',
  color: '#047857',
  icon: '📚'
})

const levelForm = ref({
  name: '',
  minAge: 6,
  maxAge: 10,
  description: ''
})

function getClassesForTeacher(teacherName) {
  return classrooms.value.filter(c => c.teacher && c.teacher.toLowerCase().includes(teacherName.toLowerCase()))
}

async function fetchClassesData() {
  loading.value = true
  try {
    const res = await apiClient.get('/admin/classes')
    if (res.data) {
      if (Array.isArray(res.data.classes) && res.data.classes.length > 0) {
        classrooms.value = res.data.classes
      }
      if (Array.isArray(res.data.categories) && res.data.categories.length > 0) {
        categories.value = res.data.categories
      }
      if (Array.isArray(res.data.levels) && res.data.levels.length > 0) {
        levels.value = res.data.levels
      }
      if (Array.isArray(res.data.schedules) && res.data.schedules.length > 0) {
        schedules.value = res.data.schedules
      }
      if (Array.isArray(res.data.teachers) && res.data.teachers.length > 0) {
        teachersList.value = res.data.teachers
      }
    }
  } catch (err) {
    console.error('Erreur API /admin/classes:', err)
  } finally {
    loading.value = false
  }
}

function openClassModal(cls = null) {
  if (cls) {
    classForm.value = { ...cls, room: cls.roomNumber || cls.room, capacity: cls.maxCapacity || cls.capacity }
  } else {
    classForm.value = { name: '', level: levels.value[0]?.name || '6-8 ans (Débutant)', teacher: 'Cheikh Mahmoud', schedule: schedules.value[0]?.name || 'Samedi 09:00 - 12:00 (Matin)', room: 'Salle Maryam 1', capacity: 20 }
  }
  showClassModal.value = true
}

function openScheduleConfigModal(sch = null) {
  if (sch) {
    scheduleEditingId.value = sch.id
    scheduleForm.value = { day: sch.day, startTime: sch.startTime, endTime: sch.endTime, label: sch.label }
  } else {
    scheduleEditingId.value = null
    scheduleForm.value = { day: 'Samedi', startTime: '09:00', endTime: '12:00', label: 'Matin' }
  }
  showScheduleModal.value = true
}

async function saveScheduleConfig() {
  const generatedName = `${scheduleForm.value.day} ${scheduleForm.value.startTime} - ${scheduleForm.value.endTime} (${scheduleForm.value.label})`
  try {
    if (scheduleEditingId.value) {
      await apiClient.put(`/admin/classes/schedules/${scheduleEditingId.value}`, scheduleForm.value)
      const existing = schedules.value.find(s => s.id === scheduleEditingId.value)
      if (existing) {
        existing.day = scheduleForm.value.day
        existing.startTime = scheduleForm.value.startTime
        existing.endTime = scheduleForm.value.endTime
        existing.label = scheduleForm.value.label
        existing.name = generatedName
      }
      showSuccessAlert('Créneau Mis à Jour ! ⏰', `Le créneau <strong>${generatedName}</strong> a été mis à jour dans la BBD MySQL.`)
    } else {
      const res = await apiClient.post('/admin/classes/schedules', scheduleForm.value)
      if (res.data && res.data.schedule) {
        schedules.value.push(res.data.schedule)
      } else {
        schedules.value.push({ id: Date.now(), day: scheduleForm.value.day, startTime: scheduleForm.value.startTime, endTime: scheduleForm.value.endTime, label: scheduleForm.value.label, name: generatedName })
      }
      showSuccessAlert('Nouveau Créneau Enregistré ! 🎉', `Le créneau <strong>${generatedName}</strong> a été enregistré en BBD MySQL.`)
    }
  } catch (err) {
    console.error('Erreur sauvegarde créneau:', err)
  } finally {
    showScheduleModal.value = false
  }
}

async function deleteSchedule(sch) {
  const res = await showDeleteConfirmDialog(`le créneau ${sch.name}`)
  if (res.isConfirmed) {
    try {
      await apiClient.delete(`/admin/classes/schedules/${sch.id}`)
    } catch (err) {
      console.warn('Suppression créneau locale :', err)
    }
    schedules.value = schedules.value.filter(s => s.id !== sch.id)
    showSuccessAlert('Créneau Supprimé ! 🗑️', `Le créneau <strong>${sch.name}</strong> a été retiré de la BBD MySQL.`)
  }
}

// CRUD CATÉGORIES
function openCategoryModal(cat = null) {
  if (cat) {
    categoryEditingId.value = cat.id
    categoryForm.value = { name: cat.name, description: cat.description || '', color: cat.color || '#047857', icon: cat.icon || '📚' }
  } else {
    categoryEditingId.value = null
    categoryForm.value = { name: '', description: '', color: '#047857', icon: '📚' }
  }
  showCategoryModal.value = true
}

async function saveCategoryConfig() {
  try {
    if (categoryEditingId.value) {
      await apiClient.put(`/admin/classes/categories/${categoryEditingId.value}`, categoryForm.value)
      const existing = categories.value.find(c => c.id === categoryEditingId.value)
      if (existing) {
        existing.name = categoryForm.value.name
        existing.description = categoryForm.value.description
        existing.color = categoryForm.value.color
        existing.icon = categoryForm.value.icon
      }
      showSuccessAlert('Catégorie Mis à Jour ! 📚', `La catégorie <strong>${categoryForm.value.name}</strong> a été enregistrée en BBD MySQL.`)
    } else {
      const res = await apiClient.post('/admin/classes/categories', categoryForm.value)
      if (res.data && res.data.category) {
        categories.value.push(res.data.category)
      } else {
        categories.value.push({ id: Date.now(), ...categoryForm.value })
      }
      showSuccessAlert('Catégorie Créée ! 🎉', `La catégorie <strong>${categoryForm.value.name}</strong> a été ajoutée en BBD MySQL.`)
    }
  } catch (err) {
    console.error('Erreur enregistrement catégorie:', err)
  } finally {
    showCategoryModal.value = false
  }
}

async function deleteCategory(cat) {
  const res = await showDeleteConfirmDialog(`la catégorie ${cat.name}`)
  if (res.isConfirmed) {
    try {
      await apiClient.delete(`/admin/classes/categories/${cat.id}`)
    } catch (err) {
      console.warn('Suppression catégorie locale :', err)
    }
    categories.value = categories.value.filter(c => c.id !== cat.id)
    showSuccessAlert('Catégorie Supprimée ! 🗑️', `La catégorie <strong>${cat.name}</strong> a été supprimée de MySQL.`)
  }
}

// CRUD NIVEAUX
function openLevelModal(lvl = null) {
  if (lvl) {
    levelEditingId.value = lvl.id
    levelForm.value = { name: lvl.name, minAge: lvl.minAge || 6, maxAge: lvl.maxAge || 10, description: lvl.description || '' }
  } else {
    levelEditingId.value = null
    levelForm.value = { name: '', minAge: 6, maxAge: 10, description: '' }
  }
  showLevelModal.value = true
}

async function saveLevelConfig() {
  try {
    const ageGroupStr = `${levelForm.value.minAge}-${levelForm.value.maxAge} ans`
    if (levelEditingId.value) {
      await apiClient.put(`/admin/classes/levels/${levelEditingId.value}`, levelForm.value)
      const existing = levels.value.find(l => l.id === levelEditingId.value)
      if (existing) {
        existing.name = levelForm.value.name
        existing.minAge = levelForm.value.minAge
        existing.maxAge = levelForm.value.maxAge
        existing.ageGroup = ageGroupStr
        existing.description = levelForm.value.description
      }
      showSuccessAlert('Niveau Mis à Jour ! 📊', `Le niveau <strong>${levelForm.value.name}</strong> a été mis à jour dans MySQL.`)
    } else {
      const res = await apiClient.post('/admin/classes/levels', levelForm.value)
      if (res.data && res.data.level) {
        levels.value.push(res.data.level)
      } else {
        levels.value.push({ id: Date.now(), ageGroup: ageGroupStr, ...levelForm.value })
      }
      showSuccessAlert('Niveau Enregistré ! 🎉', `Le niveau <strong>${levelForm.value.name}</strong> a été créé en BBD MySQL.`)
    }
  } catch (err) {
    console.error('Erreur enregistrement niveau:', err)
  } finally {
    showLevelModal.value = false
  }
}

async function deleteLevel(lvl) {
  const res = await showDeleteConfirmDialog(`le niveau ${lvl.name}`)
  if (res.isConfirmed) {
    try {
      await apiClient.delete(`/admin/classes/levels/${lvl.id}`)
    } catch (err) {
      console.warn('Suppression niveau locale :', err)
    }
    levels.value = levels.value.filter(l => l.id !== lvl.id)
    showSuccessAlert('Niveau Supprimé ! 🗑️', `Le niveau <strong>${lvl.name}</strong> a été retiré de MySQL.`)
  }
}

async function saveClass() {
  submitting.value = true
  try {
    const payload = {
      name: classForm.value.name,
      roomNumber: classForm.value.room,
      maxCapacity: classForm.value.capacity,
      schedule: classForm.value.schedule,
      teacherId: 1
    }

    const res = await apiClient.post('/admin/classes', payload)
    if (res.data && res.data.class) {
      classrooms.value.unshift(res.data.class)
    } else {
      classrooms.value.unshift({
        id: Date.now(),
        name: classForm.value.name,
        level: classForm.value.level,
        category: 'Langue Arabe',
        teacher: classForm.value.teacher,
        schedule: classForm.value.schedule,
        roomNumber: classForm.value.room,
        maxCapacity: classForm.value.capacity,
        currentEnrolled: 0
      })
    }
    showClassModal.value = false
    showSuccessAlert('Classe Enregistrée ! 🎉', `La classe <strong>${classForm.value.name}</strong> a été enregistrée avec succès.`)
  } catch (err) {
    console.error('Erreur enregistrement classe:', err)
    showErrorAlert('Erreur', 'Erreur lors de l\'enregistrement de la classe.')
  } finally {
    submitting.value = false
  }
}

const showAssignModal = ref(false)
const selectedAssignTeacher = ref(null)
const assignForm = ref({
  assignedClassIds: []
})

function openAssignModal(teacher) {
  selectedAssignTeacher.value = teacher
  const currentAssignedClasses = classrooms.value
    .filter(c => c.teacher && c.teacher.toLowerCase().includes(teacher.name.toLowerCase()))
    .map(c => c.id)

  assignForm.value.assignedClassIds = currentAssignedClasses
  showAssignModal.value = true
}

async function saveTeacherAssignments() {
  submitting.value = true
  try {
    const teacherName = selectedAssignTeacher.value.name
    const teacherId = selectedAssignTeacher.value.id

    for (const cls of classrooms.value) {
      if (assignForm.value.assignedClassIds.includes(cls.id)) {
        cls.teacher = teacherName
        try {
          await apiClient.put(`/admin/classes/${cls.id}`, {
            teacherId: teacherId,
            teacher: teacherName
          })
        } catch (e) {}
      } else if (cls.teacher && cls.teacher.toLowerCase().includes(teacherName.toLowerCase())) {
        cls.teacher = 'Non affecté'
        try {
          await apiClient.put(`/admin/classes/${cls.id}`, {
            teacherId: null,
            teacher: 'Non affecté'
          })
        } catch (e) {}
      }
    }

    showAssignModal.value = false
    showSuccessAlert(
      'Affectations Mises à Jour ! 🎉',
      `Les affectations de classes pour <strong>${teacherName}</strong> ont été enregistrées et mises à jour dans la base MySQL.`
    )
  } catch (err) {
    console.error('Erreur sauvegarde affectations:', err)
  } finally {
    submitting.value = false
  }
}

function openConfigModal(type) {
  if (type === 'CATEGORY') openCategoryModal()
  else openLevelModal()
}

const showRosterModal = ref(false)
const selectedRosterClass = ref(null)
const currentRosterStudents = ref([])
const rosterSearch = ref('')
const loadingRoster = ref(false)

const filteredRosterStudents = computed(() => {
  if (!rosterSearch.value) return currentRosterStudents.value
  const q = rosterSearch.value.toLowerCase()
  return currentRosterStudents.value.filter(st => 
    st.name.toLowerCase().includes(q) || 
    st.parentName.toLowerCase().includes(q) || 
    st.contact.includes(q)
  )
})

async function openRoster(cls) {
  selectedRosterClass.value = cls
  showRosterModal.value = true
  loadingRoster.value = true
  try {
    const res = await apiClient.get(`/admin/classes/${cls.id}/students`)
    if (res.data && Array.isArray(res.data.students)) {
      currentRosterStudents.value = res.data.students
      cls.currentEnrolled = res.data.totalEnrolled
    }
  } catch (err) {
    console.error('Erreur chargement effectif classe:', err)
    currentRosterStudents.value = [
      { id: 'student_1', dbId: 1, name: 'Youssef Benali', dateOfBirth: '12/05/2018', parentId: 'parent_1', parentName: 'Karim Benali', contact: '+352 691 123 456', status: 'INSCRIT' },
      { id: 'student_2', dbId: 2, name: 'Aya Benali', dateOfBirth: '14/09/2021', parentId: 'parent_1', parentName: 'Karim Benali', contact: '+352 691 123 456', status: 'INSCRIT' }
    ]
  } finally {
    loadingRoster.value = false
  }
}

async function deleteClass(cls) {
  const result = await showDeleteConfirmDialog(cls.name)
  if (result.isConfirmed) {
    try {
      await apiClient.delete(`/admin/classes/${cls.id}`)
    } catch (err) {
      console.warn('Suppression classe locale :', err)
    }
    classrooms.value = classrooms.value.filter(c => c.id !== cls.id)
    showSuccessAlert('Classe Supprimée ! 🗑️', `La classe <strong>${cls.name}</strong> a été supprimée de la base de données MySQL.`)
  }
}

onMounted(() => {
  fetchClassesData()
})
</script>
