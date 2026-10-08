import { useState } from "react";
import { Boxes, ArrowRight, ShieldCheck } from "lucide-react";
import { Link, useNavigate } from "react-router-dom";
import { useAuth } from "../stores/auth";
export function Login() {
  const [form, setForm] = useState({
    email: "admin@stockpilot.test",
    password: "StockPilot123!",
  });
  const [error, setError] = useState("");
  const [busy, setBusy] = useState(false);
  const login = useAuth((s) => s.login);
  const nav = useNavigate();
  async function submit(e) {
    e.preventDefault();
    setBusy(true);
    setError("");
    try {
      await login(form);
      nav("/");
    } catch (e) {
      setError(e.message);
    } finally {
      setBusy(false);
    }
  }
  return (
    <div className="grid min-h-screen overflow-hidden bg-[#07110d] text-[#e8f2eb] lg:grid-cols-2">
      <section className="relative hidden overflow-hidden border-r border-white/7 bg-[#0a1811] p-14 text-white lg:flex lg:flex-col lg:justify-between">
        <div className="absolute -left-32 top-1/3 h-96 w-96 rounded-full bg-[#59c982]/10 blur-3xl" />
        <div className="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-[#b7f34b]/10 blur-3xl" />
        <div className="relative flex items-center gap-3">
          <div className="grid h-11 w-11 place-items-center rounded-2xl bg-[#b7f34b] text-[#10200f] shadow-[0_0_32px_rgba(183,243,75,.18)]">
            <Boxes />
          </div>
          <div className="text-xl font-extrabold">StockPilot</div>
        </div>
        <div className="relative max-w-xl page-enter">
          <div className="mb-7 inline-flex items-center gap-2 rounded-full border border-[#b7f34b]/15 bg-[#b7f34b]/6 px-3 py-1.5 text-xs text-[#bdd78e]">
            <ShieldCheck size={14} /> Inventory control you can trust
          </div>
          <h1 className="text-5xl font-black leading-[1.06] tracking-[-.045em]">
            Know what you have.
            <br />
            <span className="bg-gradient-to-r from-[#d8ff91] to-[#65d18a] bg-clip-text text-transparent">
              Know where it is.
            </span>
          </h1>
          <p className="mt-6 max-w-lg text-lg leading-8 text-[#7f9588]">
            Warehouse operations, purchasing, transfers, and audit-ready stock
            history in one focused workspace.
          </p>
        </div>
        <div className="relative text-sm text-[#52675b]">
          Built for precise, accountable operations.
        </div>
      </section>
      <section className="relative flex items-center justify-center p-6">
        <div className="absolute right-0 top-0 h-80 w-80 rounded-full bg-[#b7f34b]/5 blur-3xl" />
        <div className="relative w-full max-w-md page-enter">
          <div className="mb-10 lg:hidden">
            <div className="flex items-center gap-2 text-xl font-extrabold">
              <Boxes className="text-[#b7f34b]" />
              StockPilot
            </div>
          </div>
          <p className="eyebrow">Welcome back</p>
          <h2 className="mt-3 text-3xl font-black tracking-[-.03em] text-[#f2f8f3]">
            Sign in to your workspace
          </h2>
          <p className="mt-2 text-[#71857a]">
            Use your organization account to continue.
          </p>
          <form onSubmit={submit} className="mt-8 space-y-5">
            <div>
              <label className="label">Email address</label>
              <input
                className="input"
                type="email"
                value={form.email}
                onChange={(e) => setForm({ ...form, email: e.target.value })}
                required
              />
            </div>
            <div>
              <div className="flex justify-between">
                <label className="label">Password</label>
                <Link
                  to="/forgot-password"
                  className="text-sm font-semibold text-[#a8d86f] transition hover:text-[#d8ff91]"
                >
                  Forgot password?
                </Link>
              </div>
              <input
                className="input"
                type="password"
                value={form.password}
                onChange={(e) => setForm({ ...form, password: e.target.value })}
                required
              />
            </div>
            {error && (
              <div className="rounded-xl border border-rose-400/15 bg-rose-400/10 p-3 text-sm text-rose-300">
                {error}
              </div>
            )}
            <button disabled={busy} className="btn-primary w-full py-3">
              {busy ? "Signing in…" : "Sign in"}
              <ArrowRight size={17} />
            </button>
          </form>
          <p className="mt-8 text-center text-xs text-[#586d61]">
            Protected by secure, cookie-based authentication
          </p>
        </div>
      </section>
    </div>
  );
}
