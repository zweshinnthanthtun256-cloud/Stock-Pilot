import { useCallback, useEffect, useState } from 'react'
import { Save, Shield } from 'lucide-react'
import { api } from '../api/client'

const empty = { name:'', label:'', permission_ids:[] }
export function RoleManager(){
 const [roles,setRoles]=useState([]); const [permissions,setPermissions]=useState([]); const [selected,setSelected]=useState(null); const [form,setForm]=useState(empty)
 const load=useCallback(()=>Promise.all([api.get('/roles'),api.get('/permissions')]).then(([r,p])=>{setRoles(r.data.data);setPermissions(p.data.data)}),[])
 useEffect(()=>{load()},[load])
 function choose(role){setSelected(role.id);setForm({name:role.name,label:role.label,permission_ids:role.permissions.map(p=>p.id)})}
 function toggle(id){setForm(f=>({...f,permission_ids:f.permission_ids.includes(id)?f.permission_ids.filter(x=>x!==id):[...f.permission_ids,id]}))}
 async function save(e){e.preventDefault();if(selected)await api.put(`/roles/${selected}`,form);else await api.post('/roles',form);setSelected(null);setForm(empty);await load()}
 return <div className="grid gap-5 lg:grid-cols-[320px_1fr]"><div className="card p-4"><div className="mb-3 flex items-center justify-between"><h2 className="font-bold">Roles</h2><button className="text-sm font-semibold text-emerald-700" onClick={()=>{setSelected(null);setForm(empty)}}>+ New</button></div><div className="space-y-2">{roles.map(r=><button key={r.id} onClick={()=>choose(r)} className={`flex w-full items-center gap-3 rounded-xl p-3 text-left ${selected===r.id?'bg-emerald-50 text-emerald-800':'hover:bg-slate-50'}`}><Shield size={17}/><span><strong className="block text-sm">{r.label}</strong><span className="text-xs text-slate-500">{r.permissions.length} permissions</span></span></button>)}</div></div><form onSubmit={save} className="card p-6"><h2 className="text-xl font-bold">{selected?'Edit role':'Create role'}</h2><div className="mt-5 grid gap-4 sm:grid-cols-2"><div><label className="label">Role name</label><input required disabled={Boolean(selected)} className="input" value={form.name} onChange={e=>setForm({...form,name:e.target.value})}/></div><div><label className="label">Display label</label><input required className="input" value={form.label} onChange={e=>setForm({...form,label:e.target.value})}/></div></div><h3 className="mt-7 font-bold">Permissions</h3><div className="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">{permissions.map(p=><label key={p.id} className="flex items-center gap-2 rounded-lg border p-3 text-sm"><input type="checkbox" checked={form.permission_ids.includes(p.id)} onChange={()=>toggle(p.id)}/>{p.label}</label>)}</div><div className="mt-6 flex justify-end"><button className="btn-primary"><Save size={16}/>Save role</button></div></form></div>
}
