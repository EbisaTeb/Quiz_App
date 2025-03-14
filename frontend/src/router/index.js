import { createRouter, createWebHistory } from "vue-router";
import Dashboard from "../views/Dashboard.vue";
import NotFound from "../views/NotFound.vue";
import Login from "../views/Login.vue";
import RequestPassword from "../views/RequestPassword.vue";
import ResetPassword from "../views/ResetPassword.vue";
import AppLayout from "../components/AppLayout.vue";
import Signup from "@/views/Signup.vue";
import { useAuthStore } from "@/stores/auth";
import UserManagement from "@/views/UserManagement.vue";
import Class from "@/views/Class.vue";
import Subject from "@/views/Subject.vue";
import AddStuddent from "@/views/AddStuddent.vue";
import AddTeacher from "@/views/AddTeacher.vue";
import Studentlayout from "@/components/Studentlayout.vue";
import Quiz from "@/views/Quiz.vue";
import Question from "@/views/Question.vue";
import QuizDetail from "../views/QuizDetail.vue";
import AdminQuizManagement from "@/views/AdminQuizManagement.vue";
import ActiveQuizzes from "@/views/ActiveQuizzes.vue";
import QuizSubmission from "@/views/QuizSubmission.vue";
import SubmissionDetail from "@/views/SubmissionDetail.vue";
import SubmissionList from "@/views/SubmissionList.vue";
import ShortAnswerScoring from "@/views/ShortAnswerScoring.vue";
import AdminSeeStudentscore from "@/views/AdminSeeStudentscore.vue";
import TeacherSeeStudentscore from "@/views/TeacherSeeStudentscore.vue";
import HomeView from "@/views/HomeView.vue";
import Profile from "@/views/Profile.vue";


const routes = [
  { path: "/", redirect: { name: "login" } }, // Redirect root to login
  {
    path: "/app",
    name: "app",
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      {path: "home", name: "app.home" , component: HomeView},
      { path: "dashboard", name: "app.dashboard", component: Dashboard },
      { path: "class", name: "app.class", component: Class},
      { path: "subject", name: "app.subject", component: Subject},
      { path: "usermanage", name: "app.usermanage", component: UserManagement},
      { path: "addstudent", name: "app.addstudent", component: AddStuddent},
      { path: "addteacher", name: "app.addteacher", component: AddTeacher},
      { path: "quiz", name: "app.quiz", component: Quiz},
      { path: "question", name: "app.question", component: Question},
      { path: "adminquizmanagement", name: "app.adminquizmanagement", component: AdminQuizManagement},
      { path: "active-quizzes", name: "app.activequizzes", component: ActiveQuizzes },
      { path: "quizsubmission/:id", name: "app.quizsubmission", component: QuizSubmission },
      { path: "submissions/:id", name: "app.submissiondetail", component: SubmissionDetail },
      {path: "submissionslist",name:"app.submissionslist",component:SubmissionList},
      { path: "short-answer-scoring", name: "app.shortanswerscoring", component: ShortAnswerScoring },
      { path: "admin_see_studentscore", name: "app.admin_see_studentscore", component: AdminSeeStudentscore },
      { path: "teacher_see_studentscore", name: "app.teacher_see_studentscore", component: TeacherSeeStudentscore },
      { path: "/quiz/:id", name: "app.quizDetail", component: QuizDetail},
      { path: "profile", name: "app.profile", component: Profile},

      
    ],
  },
  {path:"/student",name:"student",component:Studentlayout},
  { path: "/login", name: "login", component: Login, meta: { requiresGuest: true } },
  { path: "/signup",name:'signup',component:Signup,meta:{requiresGuest:true}},
  { path: "/request-password", name: "requestPassword", component: RequestPassword, meta: { requiresGuest: true } },
  { path: "/reset-password/:token", name: "resetPassword", component: ResetPassword, meta: { requiresGuest: true } },
  { path: "/:pathMatch(.*)*", name: "notfound", component: NotFound },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach(async (to, from, next) => {
  const store = useAuthStore();
  
  // Perform auth check to update the store's state
  await store.authCheck();

  if (to.meta.requiresAuth && !store.isAuthenticated) {
    next({ name: "login" });
  } else if (to.meta.requiresGuest && store.isAuthenticated) {
    next({ name: "app.home" });
  } else {
    next();
  }
});

export default router;
