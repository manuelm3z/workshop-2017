import React from 'react';

class TableBody extends React.Component {
	render() {
		let list = '';

		if (this.props.data) {
			list = this.props.data.map((data, i) => {
				let tds = (
					<td>data.id</td>
					);
				if (this.props.children) {
					tds = this.props.children.map((item, j) => {
						if (item.props.name === 'index') {
							return (
								<td key={j}>{i + 1}</td>
								);
						}
						if (typeof item.props.name === 'function') {
							return (
								<td key={j}>{item(data)}</td>
								);
						}
						return (
							<td key={j}>{data[item.props.name]}</td>
							);
					});
				}
				return (
					<tr key={i}>
						{tds}
					</tr>
					);
			});
		}
		return (
			<tbody>
				{list}
			</tbody>
		);
	}
}

export default TableBody;