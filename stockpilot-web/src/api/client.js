import axios from 'axios'
export const api=axios.create({baseURL:import.meta.env.VITE_API_URL||'http://localhost:8000/api/v1',withCredentials:true,headers:{Accept:'application/json'}})
api.interceptors.response.use(r=>r,error=>Promise.reject({...error,message:error.response?.data?.message||'Something went wrong. Please try again.',errors:error.response?.data?.errors||{}}))
export async function csrf(){const root=(import.meta.env.VITE_API_URL||'http://localhost:8000/api/v1').replace('/api/v1','');await axios.get(`${root}/sanctum/csrf-cookie`,{withCredentials:true})}
