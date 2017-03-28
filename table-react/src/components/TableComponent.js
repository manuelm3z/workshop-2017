import React from 'react';
import TableBody from './TableBodyComponent';
import TableHeader from './TableHeaderComponent';

class Tabl extends React.Component {
	render() {
		return (
			<table className="table table-condensed">
				<TableHeader>{this.props.children}</TableHeader>
				<TableBody data={this.props.data}>{this.props.children}</TableBody>
			</table>
		);
	}
}

export default Tabl;