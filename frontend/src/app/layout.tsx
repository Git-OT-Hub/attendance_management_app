import "./globals.scss";

const RootLayout = ({
	children,
}: Readonly<{
	children: React.ReactNode;
}>) => {
	return (
		<html lang="ja">
			<body>
				{children}
			</body>
		</html>
	);
};

export default RootLayout;