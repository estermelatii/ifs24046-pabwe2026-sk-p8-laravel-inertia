/** Siluet gedung dekoratif untuk hero (SVG murni, tanpa gambar eksternal). */
export default function Skyline({ className = '' }) {
    const towers = [
        [0, 90, 40], [46, 140, 34], [86, 70, 50], [142, 180, 44], [192, 110, 36], [234, 210, 52],
        [292, 120, 40], [338, 160, 46], [390, 90, 38], [434, 230, 56], [496, 130, 42], [544, 190, 48],
        [598, 100, 40], [644, 150, 52], [702, 80, 36], [744, 170, 44], [794, 120, 40], [840, 200, 50],
    ];

    return (
        <svg viewBox="0 0 900 260" preserveAspectRatio="xMidYMax slice" className={className} aria-hidden="true">
            {towers.map(([x, h, w], i) => (
                <g key={i}>
                    <rect x={x} y={260 - h} width={w} height={h} fill="#0b2a66" opacity={i % 2 ? 0.75 : 0.95} />
                    {Array.from({ length: Math.floor(h / 22) }).map((_, r) => (
                        <rect key={r} x={x + 6} y={260 - h + 8 + r * 22} width={w - 12} height="4" fill="#7dd3fc" opacity={(r + i) % 3 === 0 ? 0.55 : 0.12} />
                    ))}
                </g>
            ))}
        </svg>
    );
}
