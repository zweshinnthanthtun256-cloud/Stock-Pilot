import { useState } from 'react'
import { NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom'
import { Bell, Boxes, ChevronRight, ClipboardList, LayoutDashboard, LogOut, Menu, Package, Settings, ShieldCheck, ShoppingCart, Truck, Users, Warehouse, X } from 'lucide-react'
import { useAuth } from '../stores/auth'

const sections = [
  { label:'Overview', items:[['Dashboard','/',LayoutDashboard],['Notifications','/notifications',Bell]] },
  { label:'Operations', items:[['Inventory','/inventory',Boxes],['Movements','/movements',ClipboardList],['Purchases','/purchases',ShoppingCart],['Transfers','/transfers',Truck],['Sales','/sales',Package]] },
  { label:'Catalog', items:[['Products','/products',Package],['Suppliers','/suppliers',Users],['Warehouses','/warehouses',Warehouse]] },
  { label:'System', items:[['Reports','/reports',ClipboardList],['Users','/admin',Users],['Roles & permissions','/roles',ShieldCheck],['Audit logs','/audit-logs',ShieldCheck],['Settings','/settings',Settings]] },
]

export function AppLayout() {
  const [open, setOpen] = useState(false)
  const { user, logout } = useAuth()
  const location = useLocation()
  const navigate = useNavigate()
  const title = sections.flatMap(section => section.items).find(item => item[1] === location.pathname)?.[0] || 'StockPilot'

  return <div className="app-shell min-h-screen bg-[#07110d] text-[#e8f2eb]">
    <aside className={`fixed inset-y-0 left-0 z-40 w-68 border-r border-white/7 bg-[#08150f]/98 text-white shadow-[18px_0_60px_rgba(0,0,0,.2)] backdrop-blur-xl transition-transform duration-300 lg:translate-x-0 ${open?'translate-x-0':'-translate-x-full'}`}>
      <div className="flex h-20 items-center justify-between border-b border-white/7 px-5">
        <div className="flex items-center gap-3">
          <div className="grid h-10 w-10 place-items-center rounded-2xl bg-[#b7f34b] text-[#10200f] shadow-[0_0_28px_rgba(183,243,75,.18)]"><Boxes size={22}/></div>
          <div><div className="text-[17px] font-extrabold tracking-tight">StockPilot</div><div className="text-[9px] font-bold uppercase tracking-[.25em] text-[#8ca397]">Inventory OS</div></div>
        </div>
        <button aria-label="Close menu" className="rounded-xl p-2 text-white/60 hover:bg-white/8 hover:text-white lg:hidden" onClick={()=>setOpen(false)}><X size={19}/></button>
      </div>
      <nav className="h-[calc(100vh-5rem)] space-y-6 overflow-y-auto px-3 py-5">
        {sections.map(section=><div key={section.label}>
          <div className="px-3 pb-2 text-[9px] font-bold uppercase tracking-[.22em] text-[#587064]">{section.label}</div>
          {section.items.map(([label,to,Icon])=><NavLink key={to} to={to} onClick={()=>setOpen(false)} className={({isActive})=>`group mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition-all duration-200 ${isActive?'bg-[#b7f34b]/12 text-[#d8ff91] shadow-[inset_0_0_0_1px_rgba(183,243,75,.12)]':'text-[#83978b] hover:translate-x-1 hover:bg-white/5 hover:text-[#e9f4eb]'}`}>
            {({isActive})=><><span className={`grid h-8 w-8 place-items-center rounded-lg transition ${isActive?'bg-[#b7f34b] text-[#10200f]':'bg-white/5 text-[#73897c] group-hover:bg-white/8 group-hover:text-white'}`}><Icon size={16}/></span><span className="flex-1">{label}</span>{isActive&&<ChevronRight size={14}/>}</>}
          </NavLink>)}
        </div>)}
      </nav>
    </aside>

    <div className="lg:pl-68">
      <header className="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-white/7 bg-[#07110d]/82 px-4 backdrop-blur-2xl md:px-8">
        <div className="flex items-center gap-3">
          <button className="btn-secondary !p-2.5 lg:hidden" onClick={()=>setOpen(true)}><Menu size={19}/></button>
          <div><div className="flex items-center gap-1.5 text-[11px] font-medium text-[#61766a]"><span>StockPilot</span><ChevronRight size={11}/><span className="text-[#91a599]">{title}</span></div><h1 className="mt-0.5 text-xl font-extrabold tracking-tight text-[#f0f8f2]">{title}</h1></div>
        </div>
        <div className="flex items-center gap-2">
          <button aria-label="Open notifications" className="relative rounded-xl border border-white/7 bg-white/4 p-2.5 text-[#91a59a] transition hover:border-[#b7f34b]/20 hover:bg-[#b7f34b]/8 hover:text-[#d8ff91]" onClick={()=>navigate('/notifications')}><Bell size={19}/><span className="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-[#f9906f] ring-2 ring-[#07110d]"/></button>
          <div className="hidden h-8 w-px bg-white/8 sm:block"/>
          <button className="group flex items-center gap-2.5 rounded-xl border border-transparent p-1.5 transition hover:border-white/7 hover:bg-white/4" onClick={logout}>
            <span className="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-[#b7f34b] to-[#68c978] text-sm font-black text-[#10200f]">{user?.name?.[0]||'U'}</span>
            <span className="hidden text-left sm:block"><span className="block text-sm font-bold text-[#e8f2eb]">{user?.name}</span><span className="block text-[10px] font-semibold uppercase tracking-wider text-[#657a6e]">{user?.roles?.[0]?.replaceAll('-',' ')}</span></span>
            <LogOut size={15} className="text-[#50655a] transition group-hover:text-[#f18b76]"/>
          </button>
        </div>
      </header>
      <main className="p-4 md:p-8"><div key={location.pathname} className="page-enter"><Outlet/></div></main>
    </div>
    {open&&<button aria-label="Close menu overlay" className="fixed inset-0 z-30 bg-black/70 backdrop-blur-sm lg:hidden" onClick={()=>setOpen(false)}/>}
  </div>
}
