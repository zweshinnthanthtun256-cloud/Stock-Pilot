import { useState } from 'react'
import { ArrowLeft, Mail } from 'lucide-react'
import { Link } from 'react-router-dom'
import { api } from '../api/client'

export function ForgotPassword() {
  const [email,setEmail]=useState('')
  const [busy,setBusy]=useState(false)
  const [message,setMessage]=useState('')
  const [error,setError]=useState('')

  async function submit(event) {
    event.preventDefault()
    setBusy(true)
    setError('')
    setMessage('')
    try {
      const response=await api.post('/auth/forgot-password',{email})
      setMessage(response.data?.message||'If the account exists, password reset instructions have been sent.')
    } catch (requestError) {
      setError(requestError.message)
    } finally {
      setBusy(false)
    }
  }

  return <main className="grid min-h-screen place-items-center bg-[#07110d] p-6 text-[#e8f2eb]">
    <section className="card w-full max-w-md p-7 md:p-9">
      <div className="grid h-12 w-12 place-items-center rounded-2xl bg-[#b7f34b]/10 text-[#b7f34b]"><Mail size={22}/></div>
      <p className="eyebrow mt-6">Account recovery</p>
      <h1 className="mt-2 text-3xl font-black tracking-[-.03em] text-[#f2f8f3]">Reset your password</h1>
      <p className="mt-2 text-sm leading-6 text-[#71857a]">Enter the email address associated with your organization account.</p>
      <form className="mt-7 space-y-4" onSubmit={submit}>
        <div><label className="label" htmlFor="recovery-email">Email address</label><input id="recovery-email" className="input" type="email" value={email} onChange={event=>setEmail(event.target.value)} required autoComplete="email"/></div>
        {message&&<div className="rounded-xl border border-[#b7f34b]/15 bg-[#b7f34b]/8 p-3 text-sm text-[#cfee99]">{message}</div>}
        {error&&<div className="rounded-xl border border-rose-400/15 bg-rose-400/10 p-3 text-sm text-rose-300">{error}</div>}
        <button className="btn-primary w-full py-3" disabled={busy}>{busy?'Sending…':'Send reset instructions'}</button>
      </form>
      <Link className="mt-6 flex items-center justify-center gap-2 text-sm font-semibold text-[#8fa297] transition hover:text-[#d8ff91]" to="/login"><ArrowLeft size={15}/>Back to sign in</Link>
    </section>
  </main>
}
