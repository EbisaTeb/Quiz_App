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


const routes = [
  { path: "/", redirect: { name: "login" } }, // Redirect root to login
  {
    path: "/app",
    name: "app",
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      { path: "dashboard", name: "app.dashboard", component: Dashboard },
      { path: "class", name: "app.class", component: Class},
      { path: "subject", name: "app.subject", component: Subject},
      { path: "usermanage", name: "app.usermanage", component: UserManagement},
      { path: "addstudent", name: "app.addstudent", component: AddStuddent},
      { path: "addteacher", name: "app.addteacher", component: AddTeacher},
      { path: "quiz", name: "app.quiz", component: Quiz},
      { path: "question", name: "app.question", component: Question},
    ],
  },
  {path:"/student",name:"student",component:Studentlayout},
  { path: "/login", name: "login", component: Login, meta: { requiresGuest: true } },
  { path: "/signup",name:'signup',component:Signup,meta:{requiresGuest:true}},
  { path: "/request-password", name: "requestPassword", component: RequestPassword, meta: { requiresGuest: true } },
  { path: "/reset-password/:token", name: "resetPassword", component: ResetPassword, meta: { requiresGuest: true } },
  { path: "/quiz/:id", name: "quizDetail", component: QuizDetail, meta: { requiresAuth: true } },
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
    next({ name: "app.dashboard" });
  } else {
    next();
  }
});


// if (store.user?.roles.map(role => role.name).join() === "student" && to.name !== "student") {
//   next({ name: "student" });
// }
export default router;
