import { useEffect, useState } from 'react'
import { Eye, Pencil, Plus, Search, SlidersHorizontal } from 'lucide-react'
import { useNavigate } from 'react-router-dom'
import { api } from '../api/client'
import { DataTable } from '../components/DataTable'

const configs = {
  users: { title:'Users', description:'Accounts, roles, status, and warehouse access.', columns:[['name','Name'],['email','Email'],['phone','Phone'],['status','Status'],['last_login_at','Last login']] },
  sales: { title:'Sales orders', description:'Reservations and controlled stock issuing.', endpoint:'sales-orders', columns:[['number','Order'],['customer','Customer'],['warehouse_id','Warehouse'],['total','Total'],['status','Status']] },
  adjustments: { title:'Stock adjustments', description:'Controlled count corrections and approvals.', columns:[['number','Adjustment'],['product_id','Product'],['warehouse_id','Warehouse'],['difference','Difference'],['reason','Reason'],['status','Status']] },
  products: { title:'Products', description:'Manage your product catalog and stocking rules.', columns:[['sku','SKU'],['name','Product'],['barcode','Barcode'],['purchase_cost','Cost'],['selling_price','Price'],['is_active','Status']] },
  suppliers: { title:'Suppliers', description:'Vendors, contacts, and purchasing relationships.', columns:[['code','Code'],['company_name','Company'],['contact_person','Contact'],['phone','Phone'],['email','Email'],['is_active','Status']] },
  warehouses: { title:'Warehouses', description:'Locations and warehouse-level inventory control.', columns:[['code','Code'],['name','Warehouse'],['address','Location'],['phone','Phone'],['status','Status']] },
  purchases: { title:'Purchase orders', description:'Approve, order, and receive supplier purchases.', endpoint:'purchase-orders', columns:[['number','PO number'],['supplier','Supplier'],['warehouse','Warehouse'],['order_date','Order date'],['total','Total'],['status','Status']] },
  transfers: { title:'Warehouse transfers', description:'Track stock moving between locations.', columns:[['number','Transfer'],['sourceWarehouse','From'],['destinationWarehouse','To'],['created_at','Created'],['status','Status']] },
  movements: { title:'Stock movements', description:'Immutable history of every inventory change.', columns:[['reference_number','Reference'],['product','Product'],['warehouse','Warehouse'],['type','Type'],['quantity','Quantity'],['quantity_after','Balance']] },
  inventory: { title:'Inventory', description:'On-hand, reserved, and available stock by warehouse.', columns:[['product','Product'],['warehouse','Warehouse'],['on_hand','On hand'],['reserved','Reserved'],['available','Available']] },
}

export function ResourceList({ type }) {
  const navigate = useNavigate()
  const cfg = configs[type]
  const [state, setState] = useState({ rows:[], page:1, last:1, loading:true })
  const [search, setSearch] = useState('')
  const endpoint = cfg.endpoint || type
  useEffect(() => {
    const timer = setTimeout(() => {
      setState(s => ({ ...s, loading:true }))
      api.get(`/${endpoint}`, { params:{ search, page:state.page } }).then(r => {
        const d = r.data
        setState(s => ({ ...s, rows:d.data||[], page:d.current_page||1, last:d.last_page||1, loading:false }))
      }).catch(() => setState(s => ({ ...s, rows:[], loading:false })))
    }, 250)
    return () => clearTimeout(timer)
  }, [endpoint, search, state.page])
  const detailTypes=['products','purchases','transfers','sales','adjustments']
  const editableTypes=['products','suppliers','warehouses','users']
  const columns = [...cfg.columns.map(([key,label]) => ({ key, label, render:v => typeof v === 'object' ? (v?.name||v?.company_name||'—') : key === 'is_active' ? (v?'Active':'Inactive') : v })),...(detailTypes.includes(type)||editableTypes.includes(type)?[{key:'actions',label:'Actions',render:(_,row)=><div className="flex gap-1">{detailTypes.includes(type)&&<button className="rounded-lg p-2 hover:bg-slate-100" onClick={()=>navigate(`/${type}/${row.id}`)}><Eye size={16}/></button>}{editableTypes.includes(type)&&<button className="rounded-lg p-2 hover:bg-slate-100" onClick={()=>navigate(`/${type}/${row.id}/edit`)}><Pencil size={16}/></button>}</div>}]:[])]
  const createPath={products:'/products/new',suppliers:'/suppliers/new',warehouses:'/warehouses/new',users:'/users/new',purchases:'/purchases/new',transfers:'/transfers/new',sales:'/sales/new'}[type]
  return <div className="space-y-5"><div className="flex flex-wrap items-end justify-between gap-4"><div><h2 className="text-2xl font-bold tracking-tight">{cfg.title}</h2><p className="mt-1 text-sm text-[#718078]">{cfg.description}</p></div>{createPath&&<button className="btn-primary" onClick={()=>navigate(createPath)}><Plus size={17}/>Create {cfg.title.replace(/s$/,'')}</button>}</div><div className="card flex flex-wrap items-center gap-3 p-3"><div className="relative min-w-64 flex-1"><Search size={17} className="absolute left-3 top-3 text-slate-400"/><input className="input pl-9" placeholder={`Search ${cfg.title.toLowerCase()}…`} value={search} onChange={e=>{setSearch(e.target.value);setState(s=>({...s,page:1}))}}/></div><button className="btn-secondary"><SlidersHorizontal size={16}/>Filters</button></div><DataTable columns={columns} rows={state.rows} loading={state.loading} page={state.page} lastPage={state.last} onPage={page=>setState(s=>({...s,page}))}/></div>
}
