import { useEffect, useState } from 'react'
import { AlertTriangle, ArrowUpRight, Boxes, Building2, CircleDollarSign, Package, ShoppingCart, Sparkles, Truck, Users } from 'lucide-react'
import { Cell, Pie, PieChart, ResponsiveContainer, Tooltip } from 'recharts'
import { api } from '../api/client'
import { StatusBadge } from '../components/StatusBadge'

const metrics = [
  ['products','Total products',Package,'Catalog'],
  ['warehouses','Warehouses',Building2,'Network'],
  ['suppliers','Suppliers',Users,'Partners'],
  ['inventory_quantity','Units on hand',Boxes,'Live stock'],
  ['inventory_value','Inventory value',CircleDollarSign,'Asset value'],
  ['low_stock','Low stock alerts',AlertTriangle,'Attention'],
  ['pending_purchase_orders','Open purchase orders',ShoppingCart,'Inbound'],
  ['pending_transfers','Pending transfers',Truck,'In transit'],
]

const chartColors=['#b7f34b','#63d58b','#35b990','#2697a1','#5278d7','#8b69d6']

function ChartTooltip({active,payload,label}) {
  if (!active || !payload?.length) return null
  return <div className="rounded-xl border border-white/10 bg-[#08140e]/95 px-4 py-3 shadow-2xl backdrop-blur-xl"><div className="text-xs font-semibold text-[#7f9387]">{payload[0].name||label}</div><div className="mt-1 text-lg font-black text-[#d8ff91]">{Number(payload[0].value).toLocaleString()} <span className="text-xs font-medium text-[#7f9387]">units</span></div></div>
}

export function Dashboard() {
  const [data,setData]=useState(null)
  const [error,setError]=useState('')
  useEffect(()=>{api.get('/dashboard').then(r=>setData(r.data.data)).catch(e=>setError(e.message))},[])
  const warehouseRows=(data?.stock_by_warehouse||[]).map(row=>({...row,quantity:Number(row.quantity||0)}))
  const totalStock=warehouseRows.reduce((sum,row)=>sum+row.quantity,0)

  if(error) return <div className="card p-12 text-center"><AlertTriangle className="mx-auto text-amber-400"/><h2 className="mt-3 font-bold">Dashboard unavailable</h2><p className="mt-1 text-sm text-[#819389]">{error}</p></div>

  return <div className="space-y-6">
    <section className="relative overflow-hidden rounded-3xl border border-[#b7f34b]/12 bg-gradient-to-br from-[#14271b] via-[#0e1d16] to-[#0b1711] p-6 shadow-[0_24px_80px_rgba(0,0,0,.3)] md:p-8">
      <div className="absolute -right-16 -top-24 h-72 w-72 rounded-full bg-[#b7f34b]/8 blur-3xl"/>
      <div className="absolute bottom-0 right-1/4 h-24 w-56 rounded-full bg-emerald-400/6 blur-3xl"/>
      <div className="relative flex flex-wrap items-end justify-between gap-5">
        <div><div className="eyebrow flex items-center gap-2"><span className="h-2 w-2 animate-pulse rounded-full bg-[#b7f34b] shadow-[0_0_14px_#b7f34b]"/>Live operational overview</div><h2 className="mt-3 max-w-2xl text-3xl font-black tracking-[-.035em] text-[#f3f9f4] md:text-4xl">Everything in motion,<br/><span className="bg-gradient-to-r from-[#d8ff91] to-[#68d98d] bg-clip-text text-transparent">under control.</span></h2><p className="mt-3 max-w-xl text-sm leading-6 text-[#809388]">Monitor inventory, warehouse activity, and exceptions from one real-time command center.</p></div>
        <button className="btn-primary"><Sparkles size={17}/>New stock transaction<ArrowUpRight size={16}/></button>
      </div>
    </section>

    <div className="stagger-grid grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      {metrics.map(([key,label,Icon,caption])=><div key={key} className="card metric-card group p-5">
        <div className="relative z-10 flex items-start justify-between"><div><span className="text-[11px] font-bold uppercase tracking-[.13em] text-[#687d71]">{caption}</span><div className="mt-1 text-sm font-semibold text-[#9eafa5]">{label}</div></div><span className="grid h-10 w-10 place-items-center rounded-xl border border-[#b7f34b]/10 bg-[#b7f34b]/8 text-[#b7f34b] transition duration-300 group-hover:rotate-3 group-hover:scale-110 group-hover:bg-[#b7f34b] group-hover:text-[#10200f]"><Icon size={18}/></span></div>
        <div className="relative z-10 mt-5 text-3xl font-black tracking-tight text-[#f0f7f2]">{data?format(key,data.kpis[key]):<span className="inline-block h-8 w-24 rounded-lg bg-gradient-to-r from-white/4 via-white/10 to-white/4 [background-size:200%_100%] [animation:shimmer_1.5s_infinite]"/>}</div>
        <div className="relative z-10 mt-3 flex items-center gap-1.5 text-[11px] font-semibold text-[#5f7468]"><span className="h-1.5 w-1.5 rounded-full bg-[#65cf83]"/>Synced with inventory</div>
      </div>)}
    </div>

    <div className="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
      <section className="card overflow-hidden p-5 md:p-6">
        <div className="mb-3 flex items-start justify-between"><div><h3 className="panel-title">Inventory distribution</h3><p className="mt-1 text-sm text-[#75897d]">Stock allocation across your warehouse network</p></div><span className="rounded-full border border-[#b7f34b]/12 bg-[#b7f34b]/7 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-[#addc70]">Live</span></div>
        <div className="grid min-h-72 items-center gap-3 md:grid-cols-[1fr_1.1fr]">
          <div className="relative mx-auto h-64 w-full max-w-72">
            <ResponsiveContainer><PieChart><defs><filter id="donutGlow"><feGaussianBlur stdDeviation="3" result="blur"/><feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge></filter></defs><Pie data={warehouseRows} dataKey="quantity" nameKey="name" cx="50%" cy="50%" innerRadius="67%" outerRadius="88%" paddingAngle={warehouseRows.length>1?4:0} cornerRadius={8} stroke="none" animationBegin={120} animationDuration={1200} animationEasing="ease-out">{warehouseRows.map((_,index)=><Cell key={index} fill={chartColors[index%chartColors.length]} style={{filter:'url(#donutGlow)'}}/>)}</Pie><Tooltip content={<ChartTooltip/>}/></PieChart></ResponsiveContainer>
            <div className="pointer-events-none absolute inset-0 grid place-items-center text-center"><div><div className="text-3xl font-black tracking-tight text-[#f0f8f2]">{totalStock.toLocaleString()}</div><div className="mt-1 text-[10px] font-bold uppercase tracking-[.16em] text-[#63786c]">Total units</div></div></div>
          </div>
          <div className="space-y-4">{warehouseRows.map((row,index)=>{const percentage=totalStock?Math.round((Number(row.quantity)/totalStock)*100):0;return <div key={row.name} className="group"><div className="mb-2 flex items-center justify-between gap-3"><div className="flex min-w-0 items-center gap-2.5"><span className="h-2.5 w-2.5 shrink-0 rounded-full shadow-[0_0_12px_currentColor]" style={{backgroundColor:chartColors[index%chartColors.length],color:chartColors[index%chartColors.length]}}/><span className="truncate text-sm font-bold text-[#cbd8cf]">{row.name}</span></div><div className="text-right"><span className="text-sm font-black text-[#edf5ef]">{Number(row.quantity).toLocaleString()}</span><span className="ml-2 text-[10px] font-bold text-[#6d8276]">{percentage}%</span></div></div><div className="h-1.5 overflow-hidden rounded-full bg-white/5"><div className="h-full origin-left animate-[grow-x_1s_cubic-bezier(.22,1,.36,1)_both] rounded-full" style={{width:`${percentage}%`,background:`linear-gradient(90deg,${chartColors[index%chartColors.length]}99,${chartColors[index%chartColors.length]})`,animationDelay:`${220+index*90}ms`}}/></div></div>})}{data&&warehouseRows.length===0&&<div className="py-12 text-center text-sm text-[#6d8276]">No warehouse stock to chart yet.</div>}</div>
        </div>
      </section>

      <section className="card p-5 md:p-6"><div className="flex items-start justify-between"><div><h3 className="panel-title">Attention needed</h3><p className="mt-1 text-sm text-[#75897d]">Active inventory alerts</p></div><span className="grid h-9 w-9 place-items-center rounded-xl bg-amber-400/10 text-amber-300"><AlertTriangle size={17}/></span></div><div className="mt-5 space-y-3">{data?.alerts?.map((alert,index)=><div key={alert.id} style={{animationDelay:`${index*70}ms`}} className="group flex animate-[rise-in_.4s_ease_both] items-center justify-between rounded-xl border border-white/7 bg-white/[.025] p-3.5 transition hover:border-amber-300/15 hover:bg-amber-300/[.035]"><div><div className="text-sm font-bold text-[#dfeae2]">Product #{alert.product_id}</div><div className="mt-0.5 text-xs text-[#71857a]">{alert.quantity} units available</div></div><StatusBadge value={alert.level}/></div>)}{data&&!data.alerts.length&&<div className="grid min-h-52 place-items-center text-center"><div><div className="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-[#b7f34b]/8 text-[#b7f34b]"><Sparkles size={20}/></div><p className="mt-4 text-sm font-bold text-[#bfd0c5]">All levels look healthy</p><p className="mt-1 text-xs text-[#687d71]">No stock alerts need attention.</p></div></div>}</div></section>
    </div>

    <section className="card overflow-hidden"><div className="flex items-center justify-between border-b border-white/7 px-5 py-4 md:px-6"><div><h3 className="panel-title">Recent stock movements</h3><p className="mt-1 text-xs text-[#71857a]">The latest inventory events across all warehouses</p></div><Boxes size={18} className="text-[#72927e]"/></div><div className="divide-y divide-white/6">{data?.recent_movements?.map((movement,index)=><div key={movement.id} style={{animationDelay:`${index*50}ms`}} className="group flex animate-[rise-in_.4s_ease_both] items-center gap-4 px-5 py-4 transition hover:bg-white/[.025] md:px-6"><div className={`h-2.5 w-2.5 rounded-full ring-4 ${movement.quantity_after>=movement.quantity_before?'bg-[#74dd8f] ring-[#74dd8f]/10':'bg-[#f1996c] ring-[#f1996c]/10'}`}/><div className="min-w-0 flex-1"><div className="truncate text-sm font-bold text-[#dfeae2]">{movement.product?.name}</div><div className="mt-0.5 text-xs text-[#6d8276]">{movement.warehouse?.name} · {movement.reference_number}</div></div><StatusBadge value={movement.type}/><div className={`text-sm font-black ${movement.quantity_after>=movement.quantity_before?'text-[#91e5a5]':'text-[#f4a17d]'}`}>{movement.quantity_after>=movement.quantity_before?'+':'-'}{movement.quantity}</div></div>)}</div></section>
  </div>
}

function format(key,value) {
  return key==='inventory_value' ? new Intl.NumberFormat('en-US',{style:'currency',currency:'USD',maximumFractionDigits:0}).format(value||0) : new Intl.NumberFormat().format(value||0)
}
