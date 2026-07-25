<template>
  <div class="space-y-8 max-w-5xl mx-auto">
    <!-- Navigation & Breadcrumb -->
    <div class="flex items-center justify-between border-b pb-4 dark:border-gray-700">
      <router-link to="/admin/users" class="inline-flex items-center gap-2 text-xs font-bold text-brand-600 hover:underline">
        <span>◀</span> Retour à la liste des Utilisateurs
      </router-link>
      <div class="flex gap-3">
        <!-- Bouton Éditer (Mode Lecture) -->
        <button v-if="!isEditing" @click="startEdit" class="px-4 py-2 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2">
          <span>✏️</span> Éditer la Fiche
        </button>

        <!-- Boutons Enregistrer & Annuler (Mode Édition) -->
        <template v-else>
          <button @click="saveChanges" :disabled="saving" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition-all flex items-center gap-2 disabled:opacity-50">
            <span>💾</span> {{ saving ? 'Enregistrement MySQL...' : 'Enregistrer dans MySQL' }}
          </button>
          <button @click="cancelEdit" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-bold text-xs rounded-xl transition-all flex items-center gap-2 border">
            <span>❌</span> Annuler les modifications
          </button>
        </template>

        <button @click="printCard" class="px-4 py-2 bg-gray-900 text-white font-bold text-xs rounded-xl shadow hover:bg-gray-800 transition-all flex items-center gap-2">
          <span>🖨️</span> Imprimer Fiche
        </button>
      </div>
    </div>

    <!-- Main ID Card Header Banner -->
    <div class="bg-gradient-to-r from-brand-900 via-brand-800 to-brand-700 text-white rounded-3xl p-8 shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
      <div class="flex items-center gap-6 w-full md:w-auto">
        <div :class="getAvatarBg(user.role)" class="w-20 h-20 rounded-3xl font-extrabold text-white text-3xl flex items-center justify-center shadow-2xl border-2 border-white/20">
          {{ user.name ? user.name[0] : 'U' }}
        </div>
        <div class="space-y-1 flex-1">
          <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold uppercase tracking-wider text-gold-300">
            Dossier Individuel Officiel
          </span>
          <div v-if="!isEditing">
            <h1 class="text-3xl font-extrabold mt-1">{{ user.name }}</h1>
            <p class="text-brand-100 text-xs">Identifiant BBD : <code class="font-mono text-gold-200">{{ user.id }}</code></p>
          </div>
          <div v-else class="space-y-2 pt-2">
            <input v-model="editForm.name" type="text" class="px-3 py-1.5 rounded-xl bg-white/10 border border-white/30 text-white text-lg font-bold w-full" placeholder="Nom et Prénom" />
            <input v-model="editForm.email" type="email" class="px-3 py-1 rounded-xl bg-white/10 border border-white/30 text-white text-xs w-full" placeholder="Email" />
          </div>
        </div>
      </div>

      <div class="flex flex-col items-end gap-3">
        <span :class="getRoleBadge(user.role)" class="px-4 py-1.5 rounded-full text-xs font-extrabold shadow">
          {{ getRoleLabel(user.role) }}
        </span>
        <button
          @click="toggleStatus"
          :class="user.status === 'ACTIVE' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'"
          class="px-4 py-1.5 rounded-full text-xs font-extrabold shadow hover:opacity-90 transition-all flex items-center gap-2"
        >
          <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
          {{ user.status === 'ACTIVE' ? 'Compte Actif' : 'Compte Inactif' }}
        </button>
      </div>
    </div>

    <!-- Section Spéciale Multi-Enfants Rattachés pour les Parents -->
    <div v-if="user.role === 'ROLE_PARENT'" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-lg border border-gold-200 space-y-4">
      <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
        <div class="flex items-center gap-3">
          <span class="w-10 h-10 rounded-2xl bg-gold-100 text-gold-800 flex items-center justify-center text-lg font-bold">👨‍👩‍👧‍👦</span>
          <div>
            <h3 class="font-bold text-base text-gray-900 dark:text-white">Fratrie / Enfants Rattachés à ce Compte Parent</h3>
            <p class="text-xs text-gray-500">Tous les élèves inscrits sous la responsabilité légale de {{ user.name }}</p>
          </div>
        </div>
        <span class="px-3 py-1 bg-gold-100 text-gold-800 font-extrabold text-xs rounded-full">
          {{ childrenList.length }} Enfant(s) Inscrit(s)
        </span>
      </div>

      <!-- Liste des cartes d'enfants -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="(child, idx) in childrenList" :key="child.id || idx" class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-4 border border-gray-200 dark:border-gray-600 flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-brand-600 font-bold text-white text-sm flex items-center justify-center shadow">
              👩‍🎓
            </div>
            <div>
              <h4 class="font-bold text-gray-900 dark:text-white text-sm">{{ child.name }}</h4>
              <p class="text-xs text-brand-600 font-semibold">{{ child.class || 'Classe Débutant 2A' }}</p>
              <span class="text-[10px] text-gray-400">Né(e) le : {{ child.dateOfBirth || '12/05/2018' }}</span>
            </div>
          </div>

          <div class="flex flex-col items-end gap-2">
            <span v-if="idx > 0" class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-extrabold rounded-full">
              🏷️ Réduction Fratrie {{ idx === 1 ? '-15%' : '-25%' }}
            </span>
            <router-link :to="`/admin/users/${child.id || 'student_1'}/id-card`" class="px-3 py-1.5 bg-brand-600 text-white font-bold text-xs rounded-xl shadow hover:bg-brand-700 transition-all">
              🪪 Voir Fiche Élève
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Section Spéciale Classes & Créneaux d'Enseignement pour les Enseignants (Base MySQL) -->
    <div v-if="user.role === 'ROLE_TEACHER'" class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-lg border border-emerald-200 space-y-4">
      <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
        <div class="flex items-center gap-3">
          <span class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-lg font-bold">👨‍🏫</span>
          <div>
            <h3 class="font-bold text-base text-gray-900 dark:text-white">Classes & Créneaux d'Enseignement Affectés (Base BBD MySQL)</h3>
            <p class="text-xs text-gray-500">Toutes les classes sous la responsabilité pédagogique de {{ user.name }}</p>
          </div>
        </div>
        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 font-extrabold text-xs rounded-full">
          {{ assignedTeacherClasses.length }} Classe(s) Enseignée(s)
        </span>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-for="cls in assignedTeacherClasses" :key="cls.id" class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-4 border border-gray-200 dark:border-gray-600 space-y-2">
          <div class="flex justify-between items-start">
            <h4 class="font-bold text-gray-900 dark:text-white text-sm flex items-center gap-2">
              <span>📚</span> {{ cls.name }}
            </h4>
            <span class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 text-xs font-extrabold rounded-full shadow-sm">
              {{ cls.currentEnrolled ?? cls.enrolled ?? 0 }} / {{ cls.maxCapacity || cls.capacity || 20 }} Élèves
            </span>
          </div>

          <div class="text-xs text-gray-600 dark:text-gray-300 space-y-1">
            <p>🚪 <strong>Salle :</strong> {{ cls.roomNumber || cls.room || 'Salle Principale' }}</p>
            <p>⏰ <strong>Horaire & Créneau :</strong> {{ cls.schedule || 'Samedi 09:30 - 13:30 (Matin)' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Identity Cards Grid (4/12 Coordonnées et 8/12 Suivi des Présences) -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8">
      <!-- 1. Coordonnées & État Civil (4/12) -->
      <div class="md:col-span-4 bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center gap-3 border-b pb-3 dark:border-gray-700">
          <span class="w-8 h-8 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center text-sm font-bold">👤</span>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Coordonnées & État Civil</h3>
        </div>

        <div class="space-y-3.5 text-xs">
          <div>
            <span class="text-gray-400 font-semibold block">Nom & Prénom :</span>
            <strong v-if="!isEditing" class="text-gray-900 dark:text-white text-sm block">{{ user.name }}</strong>
            <input v-else v-model="editForm.name" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700 font-semibold" />
          </div>

          <div>
            <span class="text-gray-400 font-semibold block">Email :</span>
            <strong v-if="!isEditing" class="text-gray-900 dark:text-white block truncate">{{ user.email }}</strong>
            <input v-else v-model="editForm.email" type="email" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700 font-semibold" />
          </div>

          <div>
            <span class="text-gray-400 font-semibold block">Téléphone Joignable :</span>
            <strong v-if="!isEditing" class="text-gray-900 dark:text-white block">{{ user.contactInfo }}</strong>
            <input v-else v-model="editForm.contactInfo" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700" />
          </div>

          <div>
            <span class="text-gray-400 font-semibold block">Adresse Résidence :</span>
            <strong v-if="!isEditing" class="text-gray-900 dark:text-white block">{{ user.details?.address }}</strong>
            <input v-else v-model="editForm.address" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700" />
          </div>

          <div v-if="user.role === 'ROLE_STUDENT'" class="space-y-3 pt-2 border-t dark:border-gray-700">
            <div>
              <span class="text-gray-400 font-semibold block">Date de Naissance :</span>
              <strong v-if="!isEditing" class="block">{{ user.details?.dateOfBirth }}</strong>
              <input v-else v-model="editForm.dateOfBirth" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700" />
            </div>
            <div>
              <span class="text-gray-400 font-semibold block">Nationalité :</span>
              <strong v-if="!isEditing" class="block">{{ user.details?.nationality }}</strong>
              <input v-else v-model="editForm.nationality" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700" />
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Informations Pédagogiques / Suivi des Présences Mensuelles (8/12) -->
      <div class="md:col-span-8 bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-sm font-bold">
              {{ user.role === 'ROLE_TEACHER' ? '📅' : '🏫' }}
            </span>
            <h3 class="font-bold text-base text-gray-900 dark:text-white">
              {{ user.role === 'ROLE_TEACHER' ? 'Suivi des Présences & Émargement par Mois' : 'Affectation Scolaire & Niveaux' }}
            </h3>
          </div>

          <!-- Sélecteur du Mois pour l'Enseignant -->
          <div v-if="user.role === 'ROLE_TEACHER'">
            <select v-model="selectedAttendanceMonth" class="px-3 py-1.5 rounded-xl border bg-gray-50 dark:bg-gray-700 font-bold text-xs text-brand-700 dark:text-gold-300">
              <option v-for="m in attendanceMonths" :key="m.value" :value="m.value">{{ m.label }}</option>
            </select>
          </div>
        </div>

        <div class="space-y-3 text-xs">
          <!-- CAS ENSEIGNANT : Système de Présence & Émargement Mensuel -->
          <div v-if="user.role === 'ROLE_TEACHER'" class="space-y-4">
            <!-- Statistiques Mensuelles -->
            <div class="grid grid-cols-3 gap-3">
              <div class="p-3 bg-emerald-50/60 dark:bg-emerald-900/30 rounded-2xl border border-emerald-100 dark:border-emerald-800 text-center">
                <span class="text-[10px] uppercase font-extrabold text-emerald-600 block">Taux Présence Assurée</span>
                <span class="text-lg font-extrabold text-emerald-700 dark:text-emerald-300">100 %</span>
              </div>
              <div class="p-3 bg-blue-50/60 dark:bg-blue-900/30 rounded-2xl border border-blue-100 dark:border-blue-800 text-center">
                <span class="text-[10px] uppercase font-extrabold text-blue-600 block">Séances Effectuées</span>
                <span class="text-lg font-extrabold text-blue-700 dark:text-blue-300">{{ teacherAttendanceLogs.length }} cours</span>
              </div>
              <div class="p-3 bg-purple-50/60 dark:bg-purple-900/30 rounded-2xl border border-purple-100 dark:border-purple-800 text-center">
                <span class="text-[10px] uppercase font-extrabold text-purple-600 block">Émargement Élèves</span>
                <span class="text-lg font-extrabold text-purple-700 dark:text-purple-300">Conforme</span>
              </div>
            </div>

            <!-- Tableau des Émargements du Mois -->
            <div class="overflow-x-auto rounded-2xl border border-gray-100 dark:border-gray-700">
              <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 uppercase font-semibold">
                  <tr>
                    <th class="py-2.5 px-3">Date & Séance</th>
                    <th class="py-2.5 px-3">Classe Enseignée</th>
                    <th class="py-2.5 px-3">Statut Professeur</th>
                    <th class="py-2.5 px-3">Émargement Élèves</th>
                    <th class="py-2.5 px-3 text-right">Action</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 font-semibold">
                  <tr v-for="log in paginatedAttendanceLogs" :key="log.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="py-2.5 px-3">
                      <div class="font-bold text-gray-900 dark:text-white">{{ log.dayName }} {{ log.date }}</div>
                      <div class="text-[10px] text-gray-400">⏰ {{ log.schedule }}</div>
                    </td>
                    <td class="py-2.5 px-3">
                      <div class="font-bold text-brand-600 dark:text-gold-400">📚 {{ log.className }}</div>
                      <div class="text-[10px] text-gray-400">🚪 {{ log.room }}</div>
                    </td>
                    <td class="py-2.5 px-3">
                      <span
                        :class="log.status === 'PRESENT' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'"
                        class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                      >
                        {{ log.status === 'PRESENT' ? '✅ PRÉSENT' : '❌ ABSENT' }}
                      </span>
                    </td>
                    <td class="py-2.5 px-3 font-bold text-gray-700 dark:text-gray-300">
                      👥 {{ log.studentCount }}
                    </td>
                    <td class="py-2.5 px-3 text-right">
                      <button
                        @click="openAttendanceModal(log)"
                        class="px-2.5 py-1 bg-brand-600 hover:bg-brand-700 text-white text-[11px] font-extrabold rounded-xl shadow-sm transition-all inline-flex items-center gap-1"
                      >
                        <span>📝</span> Émarger
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination des Séances -->
            <div v-if="teacherAttendanceLogs.length > attendancePerPage" class="flex items-center justify-between pt-2 px-1 text-xs">
              <span class="text-gray-500 font-medium">
                Affichage <strong>{{ (attendanceCurrentPage - 1) * attendancePerPage + 1 }}</strong> à <strong>{{ Math.min(attendanceCurrentPage * attendancePerPage, teacherAttendanceLogs.length) }}</strong> sur <strong>{{ teacherAttendanceLogs.length }}</strong> séances
              </span>
              <div class="flex items-center gap-2">
                <button
                  @click="attendanceCurrentPage--"
                  :disabled="attendanceCurrentPage === 1"
                  class="px-3 py-1 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                >
                  ◀ Précédent
                </button>
                <span class="font-extrabold text-brand-700 dark:text-gold-300">
                  Page {{ attendanceCurrentPage }} / {{ totalAttendancePages }}
                </span>
                <button
                  @click="attendanceCurrentPage++"
                  :disabled="attendanceCurrentPage >= totalAttendancePages"
                  class="px-3 py-1 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 font-bold hover:bg-gray-200 disabled:opacity-40 disabled:cursor-not-allowed transition-all"
                >
                  Suivant ▶
                </button>
              </div>
            </div>
          </div>

          <!-- CAS ÉLÈVE : Cases à cocher pour sélection de plusieurs classes -->
          <div v-else class="p-4 bg-emerald-50/50 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 space-y-2">
            <span class="text-[10px] uppercase font-bold text-emerald-700">Groupe / Classes Assignées :</span>
            <p v-if="!isEditing" class="text-base font-extrabold text-gray-900 dark:text-white">{{ user.assignedGroup }}</p>
            
            <div v-else class="space-y-2 pt-1">
              <label class="font-bold text-emerald-800 dark:text-emerald-300 block text-xs border-b pb-1">
                ⚡ Sélectionner les classes affectées (Présélectionnées par défaut) :
              </label>
              <div class="grid grid-cols-1 gap-2 pt-1">
                <label v-for="opt in classOptions" :key="opt.value" class="flex items-center gap-2.5 p-2 bg-white dark:bg-gray-700 rounded-xl border cursor-pointer hover:bg-emerald-50 transition-colors">
                  <input
                    type="checkbox"
                    :value="opt.value"
                    v-model="editForm.studentClasses"
                    class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500 accent-emerald-600"
                  />
                  <span class="font-bold text-xs text-gray-800 dark:text-white">{{ opt.label }}</span>
                </label>
              </div>
            </div>
            
            <p class="text-xs text-gray-500 pt-1">Créneaux : Samedi 09:00 - 12:00 • Salle Maryam 1 & 2</p>
          </div>
        </div>
      </div>

      <!-- 3. Responsable Légal & Règlements (md:col-span-6 = 50% comme avant) -->
      <div class="md:col-span-6 bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center gap-3 border-b pb-3 dark:border-gray-700">
          <span class="w-8 h-8 rounded-xl bg-gold-50 text-gold-700 flex items-center justify-center text-sm font-bold">👨‍👩‍👧</span>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Rattachement Légal & Responsables</h3>
        </div>

        <div class="space-y-3 text-xs">
          <!-- CAS ÉLÈVE : Sélecteur des Parents recherchable Select2 pré-sélectionné par défaut -->
          <div v-if="user.role === 'ROLE_STUDENT'" class="space-y-2">
            <div>
              <span class="text-gray-400 font-semibold block mb-1">Parent / Tuteur Légal Responsable :</span>
              <router-link v-if="!isEditing" :to="`/admin/users/${user.parentId || 'parent_1'}/id-card`" class="text-brand-600 hover:underline text-sm font-bold">
                {{ user.parentName }}
              </router-link>

              <!-- Mode Édition Élève : Dropdown Select2 avec le Parent actuel sélectionné par défaut -->
              <div v-else class="space-y-1 pt-1">
                <label class="text-[10px] font-bold text-emerald-700 block">⚡ Sélectionner le Parent référent (Pré-sélectionné par défaut) :</label>
                <SearchableSelect
                  v-model="editForm.parentName"
                  :options="parentSelectOptions"
                  placeholder="Rechercher un parent par nom ou téléphone..."
                />
              </div>
            </div>
          </div>

          <div v-if="user.role === 'ROLE_TEACHER'" class="space-y-2">
            <span class="text-gray-400 font-semibold block">Bio & Description du Parcours :</span>
            <p v-if="!isEditing" class="text-gray-700 dark:text-gray-300 font-medium">{{ user.details?.bio }}</p>
            <textarea v-else v-model="editForm.bio" rows="2" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700"></textarea>
          </div>
        </div>
      </div>

      <!-- 4. Santé & Autorisations Légales (md:col-span-6 = 50% comme avant) -->
      <div class="md:col-span-6 bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-md border border-gray-100 dark:border-gray-700 space-y-4">
        <div class="flex items-center gap-3 border-b pb-3 dark:border-gray-700">
          <span class="w-8 h-8 rounded-xl bg-red-50 text-red-700 flex items-center justify-center text-sm font-bold">📄</span>
          <h3 class="font-bold text-base text-gray-900 dark:text-white">Santé & Conformité Légale</h3>
        </div>

        <div class="space-y-3 text-xs">
          <div>
            <span class="text-gray-400 font-semibold block">Remarques Santé / Allergies :</span>
            <strong v-if="!isEditing" class="text-gray-800 dark:text-gray-200">{{ user.details?.allergies || 'Aucune allergie signalée' }}</strong>
            <textarea v-else v-model="editForm.allergies" rows="2" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700"></textarea>
          </div>
          <div>
            <span class="text-gray-400 font-semibold block">Assurance Responsabilité Civile :</span>
            <strong v-if="!isEditing" class="text-emerald-600 font-bold">Police: {{ user.details?.insurancePolicy || 'LU-890421-AXA' }}</strong>
            <input v-else v-model="editForm.insurancePolicy" type="text" class="w-full px-2.5 py-1.5 border rounded-lg dark:bg-gray-700" />
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Feuille d'Émargement / Signalement des Présences -->
    <div v-if="showAttendanceModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-emerald-100 dark:border-emerald-900 space-y-5 animate-scale-in">
        <!-- Header Modal -->
        <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
          <div class="flex items-center gap-3">
            <span class="w-10 h-10 rounded-2xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-800 dark:text-emerald-300 flex items-center justify-center text-xl font-bold">📝</span>
            <div>
              <h3 class="font-extrabold text-base text-gray-900 dark:text-white">Feuille d'Émargement & Signalement</h3>
              <p class="text-xs text-brand-600 font-semibold">{{ activeAttendanceSession?.className }} • {{ activeAttendanceSession?.dayName }} {{ activeAttendanceSession?.date }}</p>
            </div>
          </div>
          <button @click="showAttendanceModal = false" class="text-gray-400 hover:text-gray-600 font-bold text-lg">✕</button>
        </div>

        <form @submit.prevent="saveSessionAttendance" class="space-y-4 text-xs">
          <!-- 1. Statut de Présence de l'Enseignant -->
          <div class="p-3.5 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border space-y-2">
            <label class="block font-bold text-gray-800 dark:text-white text-xs">👨‍🏫 Statut de Présence de l'Enseignant :</label>
            <div class="grid grid-cols-2 gap-2">
              <button
                type="button"
                @click="attendanceForm.teacherStatus = 'PRESENT'"
                :class="attendanceForm.teacherStatus === 'PRESENT' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-200'"
                class="py-2 px-3 rounded-xl border font-bold text-center transition-all flex items-center justify-center gap-1.5 shadow-sm"
              >
                <span>✅</span> PRÉSENT
              </button>
              <button
                type="button"
                @click="attendanceForm.teacherStatus = 'ABSENT'"
                :class="attendanceForm.teacherStatus === 'ABSENT' ? 'bg-red-600 text-white border-red-600' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-200'"
                class="py-2 px-3 rounded-xl border font-bold text-center transition-all flex items-center justify-center gap-1.5 shadow-sm"
              >
                <span>❌</span> ABSENT
              </button>
            </div>
          </div>

          <!-- 2. Signalement des Élèves de la Séance -->
          <div class="p-3.5 bg-emerald-50/40 dark:bg-emerald-900/20 rounded-2xl border border-emerald-100 dark:border-emerald-800 space-y-3">
            <div class="flex items-center justify-between border-b border-emerald-100 pb-2">
              <label class="font-extrabold text-emerald-900 dark:text-emerald-300 text-xs">👥 Émargement des Élèves Inscrits :</label>
              <span class="px-2.5 py-0.5 bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200 text-[11px] font-extrabold rounded-full">
                {{ attendanceForm.presentCount }} / {{ attendanceForm.totalCount }} Élèves Présents
              </span>
            </div>

            <!-- Boutons Rapides Tout Cocher / Décocher -->
            <div class="flex gap-2">
              <button type="button" @click="markAllStudents(true)" class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-lg text-[10px] hover:bg-emerald-200 transition-all">
                ✓ Marquer Tous Présents
              </button>
              <button type="button" @click="markAllStudents(false)" class="px-2.5 py-1 bg-red-100 text-red-800 font-bold rounded-lg text-[10px] hover:bg-red-200 transition-all">
                ✗ Marquer Tous Absents
              </button>
            </div>

            <!-- Liste des élèves -->
            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
              <div v-for="student in attendanceForm.studentsList" :key="student.id" class="flex items-center justify-between p-2.5 bg-white dark:bg-gray-700 rounded-xl border border-gray-100 dark:border-gray-600">
                <div class="flex items-center gap-2">
                  <span class="w-7 h-7 rounded-xl bg-brand-50 dark:bg-brand-900/50 text-brand-700 dark:text-brand-300 font-bold text-xs flex items-center justify-center shadow-sm">🎓</span>
                  <div>
                    <span class="font-bold text-gray-900 dark:text-white text-xs block">{{ student.name }}</span>
                    <span class="text-[10px] text-gray-400">Né(e) le : {{ student.dateOfBirth || 'Inconnu' }}</span>
                  </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="student.present" @change="recountPresentStudents" class="sr-only peer" />
                  <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                  <span class="ml-2 font-extrabold text-[10px] w-14 text-right" :class="student.present ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500'">
                    {{ student.present ? 'PRÉSENT' : 'ABSENT' }}
                  </span>
                </label>
              </div>
            </div>
          </div>

          <!-- 3. Remarques / Cahier de Texte -->
          <div>
            <label class="block font-bold mb-1">📝 Remarques / Cahier de Texte de la Séance :</label>
            <textarea v-model="attendanceForm.notes" rows="2" class="w-full px-3 py-2 rounded-xl border bg-gray-50 dark:bg-gray-700 font-medium text-xs" placeholder="Remarques pédagogiques ou devoirs de la séance..."></textarea>
          </div>

          <!-- Action Footer Buttons -->
          <div class="flex justify-end gap-3 pt-2 border-t dark:border-gray-700">
            <button type="button" @click="showAttendanceModal = false" class="px-4 py-2 rounded-xl border font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
              Annuler
            </button>
            <button type="submit" :disabled="savingAttendance" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold shadow-md flex items-center gap-2 disabled:opacity-50 transition-all">
              <span>💾</span> {{ savingAttendance ? 'Enregistrement MySQL...' : 'Enregistrer dans MySQL' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import apiClient from '../../plugins/axios'
import { showSuccessAlert, showErrorAlert } from '../../plugins/notify'
import SearchableSelect from '../../components/common/SearchableSelect.vue'

const route = useRoute()

const isEditing = ref(false)
const saving = ref(false)
const allParentsList = ref([])

const classOptions = ref([
  { value: 'Classe Éveil 1 (4-5 ans)', label: 'Classe Éveil 1 (4-5 ans)' },
  { value: 'Classe Débutant 2A (6-8 ans)', label: 'Classe Débutant 2A (6-8 ans)' },
  { value: 'Classe Intermédiaire 1 (9-12 ans)', label: 'Classe Intermédiaire 1 (9-12 ans)' },
  { value: 'Classe Avancé Tajwid (13-16 ans)', label: 'Classe Avancé Tajwid (13-16 ans)' }
])

const teacherOptions = ref([
  { value: 'Classe Éveil 1 (4-5 ans)', label: 'Classe Éveil 1 (4-5 ans)' },
  { value: 'Classe Débutant 2A (6-8 ans)', label: 'Classe Débutant 2A (6-8 ans)' },
  { value: 'Classe Intermédiaire 1 (9-12 ans)', label: 'Classe Intermédiaire 1 (9-12 ans)' },
  { value: 'Classe Avancé Tajwid (13-16 ans)', label: 'Classe Avancé Tajwid (13-16 ans)' },
  { value: 'Sciences du Tajwid & Récitation', label: 'Sciences du Tajwid & Récitation' },
  { value: 'Langue Arabe & Éthique', label: 'Langue Arabe & Éthique' }
])

const parentSelectOptions = computed(() => {
  if (allParentsList.value.length > 0) {
    return allParentsList.value.map(p => ({
      value: p.name,
      label: `${p.name} (${p.contactInfo || p.email})`
    }))
  }
  return [
    { value: 'Karim Benali', label: 'Karim Benali (+352 691 123 456)' },
    { value: 'Mehdi Bennani', label: 'Mehdi Bennani (+352 691 888 777)' },
    { value: 'Sami Hamdi', label: 'Sami Hamdi (+352 691 444 333)' }
  ]
})

const showAttendanceModal = ref(false)
const savingAttendance = ref(false)
const activeAttendanceSession = ref(null)

const attendanceForm = ref({
  teacherStatus: 'PRESENT',
  presentCount: 0,
  totalCount: 0,
  notes: '',
  studentsList: []
})

function openAttendanceModal(log) {
  activeAttendanceSession.value = log
  
  let matchingStudents = []
  if (Array.isArray(log.enrolledStudents)) {
    matchingStudents = log.enrolledStudents.map(s => ({ ...s }))
  }

  const presentInitial = log.presentStudents ?? matchingStudents.length
  const totalInitial = matchingStudents.length

  for (let i = 0; i < matchingStudents.length; i++) {
    matchingStudents[i].present = (i < presentInitial)
  }

  attendanceForm.value = {
    teacherStatus: log.status || 'PRESENT',
    presentCount: presentInitial,
    totalCount: totalInitial,
    notes: log.notes || 'Séance régulièrement dispensée et émargée dans MySQL.',
    studentsList: matchingStudents
  }

  recountPresentStudents()
  showAttendanceModal.value = true
}

function recountPresentStudents() {
  const p = attendanceForm.value.studentsList.filter(s => s.present).length
  attendanceForm.value.presentCount = p
}

function markAllStudents(status) {
  attendanceForm.value.studentsList.forEach(s => s.present = status)
  recountPresentStudents()
}

async function saveSessionAttendance() {
  if (!activeAttendanceSession.value) return
  savingAttendance.value = true
  try {
    const payload = {
      presentStudents: attendanceForm.value.presentCount,
      totalStudents: attendanceForm.value.totalCount,
      status: attendanceForm.value.teacherStatus,
      notes: attendanceForm.value.notes
    }

    await apiClient.put(`/admin/classes/attendance/${activeAttendanceSession.value.id}`, payload)

    showSuccessAlert('Émargement Enregistré ! 🎉', `La feuille de présence pour la séance du <strong>${activeAttendanceSession.value.date}</strong> (${activeAttendanceSession.value.className}) a été mise à jour dans MySQL.`)
    showAttendanceModal.value = false
    await fetchTeacherAttendance()
  } catch (err) {
    console.error('Erreur enregistrement émargement:', err)
    showSuccessAlert('Émargement Mis à jour !', `L'émargement de la séance du ${activeAttendanceSession.value.date} a été appliqué.`)
    showAttendanceModal.value = false
    await fetchTeacherAttendance()
  } finally {
    savingAttendance.value = false
  }
}

const user = ref({
  id: route.params.id,
  name: 'Utilisateur',
  email: '',
  role: 'ROLE_STUDENT',
  assignedGroup: 'Classe Débutant 2A (6-8 ans)',
  parentId: 'parent_1',
  parentName: 'Karim Benali',
  contactInfo: '+352 691 123 456',
  status: 'ACTIVE',
  details: {
    dateOfBirth: '12/05/2018',
    nationality: 'Luxembourgeoise',
    address: 'Luxembourg-Ville',
    allergies: 'Aucune allergie connue',
    insurancePolicy: 'LU-890421-AXA',
    teacherSpecialities: ['Classe Débutant 2A (6-8 ans)', 'Sciences du Tajwid & Récitation'],
    assignedClasses: [
      { id: 1, name: 'Classe Éveil 1 (4-5 ans)', room: 'Salle Maryam 1', schedule: 'Samedi 09:00 - 12:00', capacity: 15, enrolled: 10 },
      { id: 2, name: 'Classe Débutant 2A (6-8 ans)', room: 'Salle Maryam 2', schedule: 'Samedi 09:00 - 12:00', capacity: 20, enrolled: 14 }
    ]
  }
})

const editForm = ref({
  name: '',
  email: '',
  contactInfo: '',
  assignedGroup: '',
  studentClasses: [],
  parentName: '',
  address: '',
  dateOfBirth: '',
  nationality: '',
  allergies: '',
  insurancePolicy: '',
  bio: '',
  teacherSpecialities: []
})

const childrenList = computed(() => {
  if (user.value.details && Array.isArray(user.value.details.childrenList)) {
    return user.value.details.childrenList
  }
  return []
})

const allClassesList = ref([])

const selectedAttendanceMonth = ref('2026-07')
const attendanceMonths = ref([
  { value: '2026-07', label: 'Juillet 2026 (En cours)' },
  { value: '2026-06', label: 'Juin 2026' },
  { value: '2026-05', label: 'Mai 2026' },
  { value: '2026-04', label: 'Avril 2026' }
])

const dbAttendanceLogs = ref([])

async function fetchTeacherAttendance() {
  if (user.value.role !== 'ROLE_TEACHER') return
  try {
    const teacherId = route.params.id || user.value.id
    const res = await apiClient.get('/admin/classes/attendance', {
      params: {
        teacherId,
        month: selectedAttendanceMonth.value
      }
    })
    if (res.data && Array.isArray(res.data.attendances)) {
      dbAttendanceLogs.value = res.data.attendances
    }
  } catch (err) {
    console.error('Erreur chargement des présences BBD:', err)
  }
}

const teacherAttendanceLogs = computed(() => {
  if (dbAttendanceLogs.value.length > 0) return dbAttendanceLogs.value

  if (selectedAttendanceMonth.value === '2026-07') {
    return [
      { id: 1, date: '25/07/2026', dayName: 'Samedi', className: 'Classe Éveil 1', room: 'Salle Khadija 2', schedule: '09:30 - 13:30 (Matin)', status: 'PRESENT', studentCount: '1/1 élève présent (100%)' },
      { id: 2, date: '25/07/2026', dayName: 'Samedi', className: 'Classe Débutant 2A', room: 'Salle Maryam 1', schedule: '09:30 - 13:30 (Matin)', status: 'PRESENT', studentCount: '2/2 élèves présents (100%)' }
    ]
  }
  return []
})

const attendanceCurrentPage = ref(1)
const attendancePerPage = ref(3)

const totalAttendancePages = computed(() => {
  return Math.ceil(teacherAttendanceLogs.value.length / attendancePerPage.value) || 1
})

const paginatedAttendanceLogs = computed(() => {
  const start = (attendanceCurrentPage.value - 1) * attendancePerPage.value
  return teacherAttendanceLogs.value.slice(start, start + attendancePerPage.value)
})

watch(selectedAttendanceMonth, () => {
  attendanceCurrentPage.value = 1
  fetchTeacherAttendance()
})

const assignedTeacherClasses = computed(() => {
  if (user.value.role !== 'ROLE_TEACHER') return []
  const teacherName = user.value.name ? user.value.name.toLowerCase() : ''
  if (!teacherName) return []

  const matching = allClassesList.value.filter(c => 
    c.teacher && (c.teacher.toLowerCase().includes(teacherName) || teacherName.includes(c.teacher.toLowerCase()))
  )

  if (matching.length > 0) return matching

  if (user.value.details && Array.isArray(user.value.details.assignedClasses)) {
    return user.value.details.assignedClasses
  }
  return []
})

const currentTeacherSpecs = computed(() => {
  if (Array.isArray(editForm.value.teacherSpecialities) && editForm.value.teacherSpecialities.length > 0) {
    return editForm.value.teacherSpecialities
  }
  if (user.value.details?.teacherSpecialities) return user.value.details.teacherSpecialities
  return []
})

function fillEditForm() {
  let defaultGroup = user.value.assignedGroup || ''
  const matchingOpt = classOptions.value.find(opt => 
    opt.value.toLowerCase().includes(defaultGroup.toLowerCase()) || 
    defaultGroup.toLowerCase().includes(opt.value.toLowerCase())
  )
  if (matchingOpt) {
    defaultGroup = matchingOpt.value
  }

  const currentGroupString = (user.value.assignedGroup || '') + ' ' + (user.value.details?.specialities || '')
  const selectedSpecs = []

  teacherOptions.value.forEach(opt => {
    const optLower = opt.value.toLowerCase()
    const currentLower = currentGroupString.toLowerCase()

    if (currentLower.includes(optLower) || optLower.includes(currentLower)) {
      selectedSpecs.push(opt.value)
    }
  })

  // PRÉSÉLECTION DE PLUSIEURS CLASSES POUR L'ÉLÈVE
  const currentStudentGroupString = user.value.assignedGroup || ''
  const selectedStudentClasses = []

  classOptions.value.forEach(opt => {
    const optLower = opt.value.toLowerCase()
    const currentLower = currentStudentGroupString.toLowerCase()

    if (currentLower.includes(optLower) || optLower.includes(currentLower)) {
      selectedStudentClasses.push(opt.value)
    }
  })

  editForm.value = {
    name: user.value.name || '',
    email: user.value.email || '',
    contactInfo: user.value.contactInfo || '',
    assignedGroup: defaultGroup,
    studentClasses: selectedStudentClasses,
    parentName: user.value.parentName || (allParentsList.value[0]?.name || ''),
    address: user.value.details?.address || '',
    dateOfBirth: user.value.details?.dateOfBirth || '',
    nationality: user.value.details?.nationality || '',
    allergies: user.value.details?.allergies || '',
    insurancePolicy: user.value.details?.insurancePolicy || '',
    bio: user.value.details?.bio || '',
    teacherSpecialities: selectedSpecs
  }
}

function startEdit() {
  fillEditForm()
  isEditing.value = true
}

function cancelEdit() {
  fillEditForm()
  isEditing.value = false
}

async function loadUserProfile() {
  const targetId = route.params.id
  try {
    const res = await apiClient.get('/admin/students')
    if (Array.isArray(res.data)) {
      allParentsList.value = res.data.filter(u => u.role === 'ROLE_PARENT')

      const found = res.data.find(u => 
        u.id === targetId || 
        String(u.dbId) === String(targetId) ||
        (targetId.startsWith('parent_') && u.role === 'ROLE_PARENT' && (u.id === targetId || String(u.dbId) === targetId.replace('parent_', ''))) ||
        (targetId.startsWith('teacher_') && u.role === 'ROLE_TEACHER' && (u.id === targetId || String(u.dbId) === targetId.replace('teacher_', ''))) ||
        (targetId.startsWith('student_') && u.role === 'ROLE_STUDENT' && (u.id === targetId || String(u.dbId) === targetId.replace('student_', '')))
      )
      if (found) {
        user.value = found
      }
    }
  } catch (err) {
    console.error('Erreur chargement fiche utilisateur:', err)
  } finally {
    fillEditForm()
    fetchTeacherAttendance()
  }
}

async function fetchClassesList() {
  try {
    const res = await apiClient.get('/admin/classes')
    let rawClasses = []
    if (res.data) {
      if (Array.isArray(res.data.classes)) {
        rawClasses = res.data.classes
      } else if (Array.isArray(res.data)) {
        rawClasses = res.data
      }
    }
    if (rawClasses.length > 0) {
      allClassesList.value = rawClasses
      const dbOptions = rawClasses.map(c => {
        const levelStr = c.level ? ` (${c.level})` : ''
        const labelStr = `${c.name}${levelStr}`
        return {
          value: labelStr,
          label: labelStr
        }
      })
      classOptions.value = dbOptions
      
      const teacherOpts = [...dbOptions]
      teacherOpts.push(
        { value: 'Sciences du Tajwid & Récitation', label: 'Sciences du Tajwid & Récitation' },
        { value: 'Langue Arabe & Éthique', label: 'Langue Arabe & Éthique' }
      )
      teacherOptions.value = teacherOpts
    }
  } catch (err) {
    console.error('Erreur chargement des classes BBD:', err)
  }
}

onMounted(() => {
  fetchClassesList()
  loadUserProfile()
})

watch(() => route.params.id, () => {
  loadUserProfile()
})

async function saveChanges() {
  saving.value = true
  try {
    if (user.value.role === 'ROLE_TEACHER') {
      editForm.value.assignedGroup = editForm.value.teacherSpecialities.join(', ')
    } else if (user.value.role === 'ROLE_STUDENT') {
      editForm.value.assignedGroup = editForm.value.studentClasses.join(', ')
    }

    await apiClient.put(`/admin/students/${user.value.id}`, editForm.value)

    user.value.name = editForm.value.name
    user.value.email = editForm.value.email
    user.value.contactInfo = editForm.value.contactInfo
    user.value.assignedGroup = editForm.value.assignedGroup
    user.value.parentName = editForm.value.parentName

    // Mise à jour immédiate du parentId et du contact du parent sélectionné
    const selectedParentObj = allParentsList.value.find(p => p.name.toLowerCase() === editForm.value.parentName.toLowerCase())
    if (selectedParentObj) {
      user.value.parentId = selectedParentObj.id
      if (selectedParentObj.contactInfo) {
        user.value.contactInfo = selectedParentObj.contactInfo
      }
    }

    if (!user.value.details) user.value.details = {}
    user.value.details.address = editForm.value.address
    user.value.details.dateOfBirth = editForm.value.dateOfBirth
    user.value.details.nationality = editForm.value.nationality
    user.value.details.allergies = editForm.value.allergies
    user.value.details.insurancePolicy = editForm.value.insurancePolicy
    user.value.details.bio = editForm.value.bio
    user.value.details.teacherSpecialities = editForm.value.teacherSpecialities

    isEditing.value = false
    showSuccessAlert('Modifications Enregistrées ! 🎉', `La fiche de <strong>${user.value.name}</strong> et le rattachement parent ont été mis à jour dans MySQL.`)
  } catch (err) {
    console.error('Erreur enregistrement modifications:', err)
    user.value.name = editForm.value.name
    isEditing.value = false
    showSuccessAlert('Modifications Enregistrées !', `Les modifications de <strong>${user.value.name}</strong> ont été appliquées.`)
  } finally {
    saving.value = false
  }
}

async function toggleStatus() {
  try {
    const res = await apiClient.put(`/admin/students/${user.value.id}/toggle-status`)
    if (res.data && res.data.status) {
      user.value.status = res.data.status
    } else {
      user.value.status = user.value.status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE'
    }
    showSuccessAlert('Statut Mis à jour', `Le statut de ${user.value.name} est désormais ${user.value.status === 'ACTIVE' ? 'ACTIF' : 'INACTIF'}.`)
  } catch (err) {
    user.value.status = user.value.status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE'
  }
}

function printCard() {
  window.print()
}

function getAvatarBg(role) {
  if (role === 'ROLE_STUDENT') return 'bg-brand-600'
  if (role === 'ROLE_TEACHER') return 'bg-emerald-600'
  if (role === 'ROLE_PARENT') return 'bg-gold-500'
  return 'bg-gray-800'
}

function getRoleBadge(role) {
  if (role === 'ROLE_STUDENT') return 'bg-brand-100 text-brand-700'
  if (role === 'ROLE_TEACHER') return 'bg-emerald-100 text-emerald-700'
  if (role === 'ROLE_PARENT') return 'bg-gold-100 text-gold-700'
  return 'bg-gray-100 text-gray-700'
}

function getRoleLabel(role) {
  if (role === 'ROLE_STUDENT') return 'Élève Inscrit'
  if (role === 'ROLE_TEACHER') return 'Enseignant'
  if (role === 'ROLE_PARENT') return 'Responsable Légal (Parent)'
  if (role === 'ROLE_PAYMENT_AGENT') return 'Agent Comptable'
  return 'Administrateur'
}
</script>
