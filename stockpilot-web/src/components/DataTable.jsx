import { ChevronLeft, ChevronRight, Inbox } from 'lucide-react'
import { StatusBadge } from './StatusBadge'

export function DataTable({columns,rows,loading,page=1,lastPage=1,onPage}) {
  if(loading) return <div className="card space-y-3 p-5">{[1,2,3,4,5].map(item=><div key={item} className="h-12 rounded-xl bg-gradient-to-r from-white/[.025] via-white/[.07] to-white/[.025] [background-size:200%_100%] [animation:shimmer_1.5s_infinite]"/>)}</div>

  return <div className="card overflow-hidden">
    <div className="overflow-x-auto"><table className="w-full text-left text-sm">
      <thead className="border-b border-white/7 bg-white/[.025] text-[10px] uppercase tracking-[.14em] text-[#6f8478]"><tr>{columns.map(column=><th key={column.key} className="whitespace-nowrap px-5 py-4 font-bold">{column.label}</th>)}</tr></thead>
      <tbody className="divide-y divide-white/6">{rows.map((row,index)=><tr key={row.id||index} style={{animationDelay:`${Math.min(index,8)*35}ms`}} className="group animate-[rise-in_.35s_ease_both] transition-colors hover:bg-[#b7f34b]/[.025]">{columns.map(column=><td key={column.key} className="whitespace-nowrap px-5 py-4 text-[#b9c8bf] transition-colors first:font-semibold first:text-[#e2ece5] group-hover:text-[#d7e4da]">{column.render?column.render(row[column.key],row):column.key==='status'?<StatusBadge value={row[column.key]}/>:row[column.key]??'—'}</td>)}</tr>)}</tbody>
    </table></div>
    {!rows.length&&<div className="flex flex-col items-center py-16 text-[#65796d]"><span className="grid h-14 w-14 place-items-center rounded-2xl border border-white/7 bg-white/[.025]"><Inbox size={25}/></span><p className="mt-4 font-bold text-[#a5b6ac]">No records found</p><p className="mt-1 text-sm">Try changing your filters.</p></div>}
    <div className="flex items-center justify-between border-t border-white/7 bg-black/5 px-5 py-3.5 text-sm text-[#71857a]"><span>Page <b className="text-[#c3d1c8]">{page}</b> of {lastPage}</span><div className="flex gap-2"><button aria-label="Previous page" className="btn-secondary !p-2" disabled={page<=1} onClick={()=>onPage?.(page-1)}><ChevronLeft size={16}/></button><button aria-label="Next page" className="btn-secondary !p-2" disabled={page>=lastPage} onClick={()=>onPage?.(page+1)}><ChevronRight size={16}/></button></div></div>
  </div>
}
