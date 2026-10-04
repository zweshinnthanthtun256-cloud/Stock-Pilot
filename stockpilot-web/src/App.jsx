import { useEffect } from 'react'
import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { AppLayout } from './layouts/AppLayout'
import { Dashboard } from './pages/Dashboard'
import { Login } from './pages/Login'
import { Placeholder } from './pages/Placeholder'
import { ResourceList } from './pages/ResourceList'
import { useAuth } from './stores/auth'

function Protected({ children }) {
  const { user, loading, restore } = useAuth()
  useEffect(() => { restore() }, [restore])
  if (loading) return <div className="grid min-h-screen place-items-center text-sm text-slate-500">Loading StockPilot…</div>
  return user ? children : <Navigate to="/login" replace />
}

export default function App() {
  return <BrowserRouter><Routes>
    <Route path="/login" element={<Login />} />
    <Route element={<Protected><AppLayout /></Protected>}>
      <Route index element={<Dashboard />} />
      <Route path="products" element={<ResourceList type="products" />} />
      <Route path="suppliers" element={<ResourceList type="suppliers" />} />
      <Route path="warehouses" element={<ResourceList type="warehouses" />} />
      <Route path="inventory" element={<ResourceList type="inventory" />} />
      <Route path="movements" element={<ResourceList type="movements" />} />
      <Route path="purchases" element={<ResourceList type="purchases" />} />
      <Route path="transfers" element={<ResourceList type="transfers" />} />
      <Route path="sales" element={<ResourceList type="sales" />} />
      <Route path="reports" element={<Placeholder title="Reports & CSV exports" />} />
      <Route path="admin" element={<ResourceList type="users" />} />
    </Route>
    <Route path="*" element={<div className="grid min-h-screen place-items-center text-center"><div><div className="text-7xl font-bold text-[#1d5b3a]">404</div><p className="mt-3 text-slate-500">That page does not exist.</p></div></div>} />
  </Routes></BrowserRouter>
}
