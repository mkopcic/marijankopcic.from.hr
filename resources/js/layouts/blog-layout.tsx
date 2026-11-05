import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, BookOpen, Briefcase, Code2, Mail, Menu, User, X } from 'lucide-react';
import { useState } from 'react';
import type { PropsWithChildren } from 'react';

import SocialLinks from '@/components/social-links';
import { toPath } from '@/lib/utils';
import { index as blogIndex } from '@/routes/blog';

/**
 * Sections of the portfolio the blog links back to.
 */
const PORTFOLIO_SECTIONS = [
    { name: 'Work', anchor: 'work', icon: Briefcase },
    { name: 'Experience', anchor: 'experience', icon: Code2 },
    { name: 'About', anchor: 'about', icon: User },
    { name: 'Contact', anchor: 'contact', icon: Mail },
];

export interface BlogSeo {
    title: string;
    description: string;
    url: string;
}

interface Props {
    seo: BlogSeo;
}

/**
 * Shell for every public blog page. Links back to the portfolio use a plain
 * anchor on purpose — it lives on a different host, and an Inertia visit
 * cannot cross origins.
 */
export default function BlogLayout({ seo, children }: PropsWithChildren<Props>) {
    const portfolioUrl = 'https://marijankopcic.from.hr';
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

    return (
        <>
            {/*
                SEO meta, Open Graph, Twitter and canonical are rendered
                server-side by seotools in resources/views/app.blade.php.
                Repeating them here emitted every tag twice — and, with SSR
                on, a second og:type that contradicted the server's one.
                Only the title stays, so client-side visits update the tab.
            */}
            <Head title={seo.title}>
                <link rel="preconnect" href="https://fonts.googleapis.com" />
                <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="anonymous" />
                <link
                    href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap"
                    rel="stylesheet"
                />
            </Head>

            <div className="min-h-screen bg-[#060e20] font-['Inter']">
                <header className="sticky top-0 z-50 bg-[#060e20]/70 shadow-[0_40px_40px_rgba(222,229,255,0.04)] backdrop-blur-xl">
                    <nav className="mx-auto flex max-w-7xl items-center justify-between gap-6 px-8 py-4 font-['Manrope'] text-sm font-medium tracking-tight">
                        <Link
                            href={toPath(blogIndex.url())}
                            className="shrink-0 text-xl font-bold tracking-tighter text-[#dee5ff] transition-colors hover:text-[#06b77f]"
                        >
                            Marijan Kopčić<span className="text-[#06b77f]">/blog</span>
                        </Link>

                        <div className="hidden items-center gap-8 lg:flex">
                            {PORTFOLIO_SECTIONS.map(({ name, anchor, icon: Icon }) => (
                                <a
                                    key={anchor}
                                    href={`${portfolioUrl}/#${anchor}`}
                                    className="flex items-center gap-1.5 text-[#91aaeb] transition-colors duration-300 hover:text-[#bdc2ff]"
                                >
                                    <Icon size={16} />
                                    {name}
                                </a>
                            ))}
                            <Link
                                href={toPath(blogIndex.url())}
                                className="flex items-center gap-1.5 border-b border-[#bdc2ff]/30 font-semibold text-[#bdc2ff]"
                            >
                                <BookOpen size={16} />
                                Blog
                            </Link>
                        </div>

                        <div className="hidden items-center gap-3 lg:flex">
                            <SocialLinks className="mr-2 hidden xl:flex" />
                            <a
                                href={portfolioUrl}
                                className="flex items-center gap-1.5 rounded-md bg-[#06b77f] px-5 py-1.5 text-sm font-medium text-[#001a12] transition-all duration-200 ease-in-out hover:scale-105 hover:bg-[#05a36f]"
                            >
                                <ArrowLeft size={16} />
                                Portfolio
                            </a>
                        </div>

                        <button
                            type="button"
                            onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                            className="text-[#dee5ff] transition-colors hover:text-[#06b77f] lg:hidden"
                            aria-label="Toggle menu"
                            aria-expanded={mobileMenuOpen}
                        >
                            {mobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
                        </button>
                    </nav>

                    {mobileMenuOpen && (
                        <div className="border-t border-[#2b4680]/20 bg-[#05183c] lg:hidden">
                            <div className="mx-auto max-w-7xl space-y-1 px-8 py-6 font-['Manrope']">
                                {PORTFOLIO_SECTIONS.map(({ name, anchor, icon: Icon }) => (
                                    <a
                                        key={anchor}
                                        href={`${portfolioUrl}/#${anchor}`}
                                        className="flex items-center gap-3 border-b border-[#2b4680]/20 py-3 text-[#91aaeb] hover:text-[#bdc2ff]"
                                    >
                                        <Icon size={20} />
                                        {name}
                                    </a>
                                ))}
                                <Link
                                    href={toPath(blogIndex.url())}
                                    className="flex items-center gap-3 border-b border-[#2b4680]/20 py-3 font-semibold text-[#bdc2ff]"
                                >
                                    <BookOpen size={20} />
                                    Blog
                                </Link>
                                <div className="flex items-center justify-between gap-4 pt-5">
                                    <SocialLinks />
                                    <a
                                        href={portfolioUrl}
                                        className="flex items-center gap-1.5 rounded-lg bg-[#06b77f] px-4 py-2 text-sm font-medium text-[#001a12]"
                                    >
                                        <ArrowLeft size={16} />
                                        Portfolio
                                    </a>
                                </div>
                            </div>
                        </div>
                    )}
                </header>

                <main className="mx-auto max-w-5xl px-6 md:px-8">{children}</main>

                <footer className="mt-24 border-t border-[#2b4680]/20 bg-[#000000]">
                    <div className="mx-auto grid max-w-6xl grid-cols-1 gap-10 px-6 py-14 text-sm text-[#91aaeb] md:grid-cols-3 md:px-8">
                        <div className="space-y-3">
                            <a href={portfolioUrl} className="font-['Manrope'] text-lg font-bold tracking-tight text-[#dee5ff] hover:text-[#06b77f]">
                                Marijan Kopčić
                            </a>
                            <p className="leading-relaxed">
                                Lead Laravel &amp; Backend Engineer. I design, ship, and operate production Laravel systems.
                            </p>
                        </div>
                        <div>
                            <h2 className="mb-4 font-['Manrope'] text-xs font-bold uppercase tracking-widest text-[#06b77f]">Portfolio</h2>
                            <ul className="space-y-2.5">
                                {PORTFOLIO_SECTIONS.map(({ name, anchor, icon: Icon }) => (
                                    <li key={anchor}>
                                        <a href={`${portfolioUrl}/#${anchor}`} className="flex items-center gap-2 transition-colors hover:text-[#bdc2ff]">
                                            <Icon size={14} />
                                            {name}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </div>
                        <div>
                            <h2 className="mb-4 font-['Manrope'] text-xs font-bold uppercase tracking-widest text-[#06b77f]">Connect</h2>
                            <SocialLinks />
                        </div>
                    </div>
                    <div className="border-t border-[#2b4680]/20 py-6 text-center text-xs text-[#91aaeb]">
                        © {new Date().getFullYear()} Marijan Kopčić
                    </div>
                </footer>
            </div>
        </>
    );
}
